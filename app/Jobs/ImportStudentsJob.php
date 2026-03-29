<?php

namespace App\Jobs;

use App\Actions\ConsultaLCS;
use App\Models\Person;
use App\Models\System\GradeSchedule;
use App\Models\Grade;
use App\Models\System\Student;
use App\Models\System\Enrollment;
use App\Models\System\Period;
use App\Http\Controllers\StudentController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;
    public int $timeout = 120;

    public function __construct(
        protected array  $row,
        protected string $batchId
    ) {}

    public function handle(StudentController $studentController): void
    {
        $identify  = $this->row['identify_number'];
        $levelName = mb_strtolower(preg_replace('/\s+/', ' ', trim($this->row['level'])), 'UTF-8');
        $gradeName = mb_strtolower(preg_replace('/\s+/', ' ', trim($this->row['grade'])), 'UTF-8');
        $section   = mb_strtolower(preg_replace('/\s+/', ' ', trim($this->row['section'])), 'UTF-8');

        // Datos manuales que pueden venir del Excel (Caso B) o ser null (Caso A)
        $manualFirstname      = $this->row['manual_firstname']       ?? null;
        $manualLastnameFather = $this->row['manual_lastname_father'] ?? null;
        $manualLastnameMom    = $this->row['manual_lastname_mom']    ?? null;
        $manualGender         = $this->row['manual_gender']          ?? null;
        $manualBirthday       = $this->row['manual_birthday']        ?? null;

        // ¿El Excel trae datos manuales suficientes para crear la persona
        // sin depender de la API? (necesitamos al menos nombre + ambos apellidos)
        $hasManualData = !empty($manualFirstname)
            && !empty($manualLastnameFather)
            && !empty($manualLastnameMom);

        try {

            /*
            ─────────────────────────────────────────
            1. PERIODO ACTIVO
            ─────────────────────────────────────────
            */

            $period = Period::where('is_active', 'Y')->first();

            if (!$period) {
                $this->recordError("No existe periodo activo configurado");
                return;
            }

            /*
            ─────────────────────────────────────────
            2. NIVEL
            ─────────────────────────────────────────
            */

            $level = \App\Models\Level::whereRaw(
                    "LOWER(TRIM(regexp_replace(name_large, '\\s+', ' ', 'g'))) = ?",
                    [$levelName]
                )
                ->whereNull('deleted_at')
                ->first();

            if (!$level) {
                $this->recordError("Nivel no encontrado: '{$levelName}' (DNI: {$identify})");
                return;
            }

            /*
            ─────────────────────────────────────────
            3. GRADO
            ─────────────────────────────────────────
            */

            $grade = Grade::whereRaw(
                    "LOWER(TRIM(regexp_replace(name_large, '\\s+', ' ', 'g'))) = ?",
                    [$gradeName]
                )
                ->where('codlevel', $level->codlevel)
                ->whereNull('deleted_at')
                ->first();

            if (!$grade) {
                $this->recordError("Grado no encontrado: '{$gradeName}' (DNI: {$identify})");
                return;
            }

            /*
            ─────────────────────────────────────────
            3. SECCION
            ─────────────────────────────────────────
            */

            $gradeSchedule = GradeSchedule::where('codgrade', $grade->codgrade)
                ->whereRaw(
                    "LOWER(TRIM(regexp_replace(section, '\\s+', ' ', 'g'))) = ?",
                    [$section]
                )
                ->whereNull('deleted_at')
                ->first();

            if (!$gradeSchedule) {
                $this->recordError("Sección '{$section}' no encontrada para '{$levelName} - {$gradeName}' (DNI: {$identify})");
                return;
            }

            /*
            ─────────────────────────────────────────
            4. PERSONA
            ─────────────────────────────────────────
            */

            $person = Person::where('identify_number', $identify)
                ->whereNull('deleted_at')
                ->first();

            if (!$person) {
                // ── Intentar consultar RENIEC / API externa ──
                $apiConsulta = new ConsultaLCS();
                $personData  = null;
                $maxAttempts = 3;

                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    try {
                        usleep(300000); // 0.3s antes de cada intento
                        $personData = $apiConsulta->ConsultaDNI($identify);
                        if ($personData) break;
                    } catch (\Throwable $e) {
                        Log::warning("ConsultaDNI intento {$attempt} falló", [
                            'dni'   => $identify,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    if ($attempt < $maxAttempts) {
                        sleep(2);
                    }
                }

                // ── Si la API no devolvió datos, usar los del Excel como fallback ──
                if (!$personData) {
                    if (!$hasManualData) {
                        // Sin datos de API y sin datos manuales suficientes → error
                        $this->recordError(
                            "No se encontraron datos RENIEC para DNI: {$identify} " .
                            "y el Excel no contiene nombre/apellidos para crearlo manualmente."
                        );
                        return;
                    }

                    Log::info("ImportStudentsJob: usando datos manuales del Excel para DNI {$identify}", [
                        'firstname'       => $manualFirstname,
                        'lastname_father' => $manualLastnameFather,
                        'lastname_mom'    => $manualLastnameMom,
                    ]);

                    // Construir $personData con los campos del Excel
                    $personData = [
                        'firstname'      => $manualFirstname,
                        'lastname_father' => $manualLastnameFather,
                        'lastname_mom'    => $manualLastnameMom,
                        'gender'         => $manualGender,       // código numérico: 1 / 2 / null
                        'birthdate'      => $manualBirthday,     // Y-m-d o null
                        'address'        => null,
                        'civil_status'   => null,
                        'ubigeo_nac'     => null,
                    ];
                }

                // ── Crear la persona (con datos de API o del Excel) ──
                $person = Person::create([
                    'codtd_identify'  => 1,
                    'identify_number' => $identify,
                    'firstname'       => $personData['firstname'],
                    'lastname_father' => $personData['lastname_father'],
                    'lastname_mom'    => $personData['lastname_mom'],
                    'address'         => $personData['address']       ?? null,
                    'birthday'        => $personData['birthdate']     ?? null,
                    'codgender'       => $personData['gender']        ?? null,
                    'codcivil_status' => $personData['civil_status']  ?? null,
                    'codubigeo'       => $personData['ubigeo_nac']    ?? null,
                    'department'      => null,
                    'province'        => null,
                    'district'        => null,
                ]);
            }

            /*
            ─────────────────────────────────────────
            5. VALIDAR DUPLICADO EN ENROLLMENT
            ─────────────────────────────────────────
            */

            $alreadyExists = Enrollment::whereHas('student', function ($q) use ($person) {
                $q->where('codperson', $person->codperson);
            })
                ->where('codgrade_schedule', $gradeSchedule->codgrade_schedule)
                ->where('codperiod', $period->codperiod)
                ->whereNull('deleted_at')
                ->exists();

            if ($alreadyExists) {
                $this->recordSkipped("Ya matriculado: DNI {$identify} en {$gradeName} - {$section}");
                return;
            }

            /*
            ─────────────────────────────────────────
            6. CREAR STUDENT + ENROLLMENT
            ─────────────────────────────────────────
            */

            DB::transaction(function () use ($person, $gradeSchedule, $period, $studentController) {

                $student = Student::firstOrCreate(
                    ['codperson' => $person->codperson],
                    ['codassignee' => null]
                );

                Enrollment::create([
                    'codstudent'        => $student->codstudent,
                    'codgrade_schedule' => $gradeSchedule->codgrade_schedule,
                    'codperiod'         => $period->codperiod,
                ]);

                // Forzar fresh load DESPUÉS de crear el enrollment
                $studentWithRelations = Student::with([
                    'person',
                    'assignee',
                    'currentEnrollment.grade_schedule.grade.level',
                    'currentEnrollment.grade_schedule.schedule',
                ])->findOrFail($student->codstudent);

                if (!$studentWithRelations->currentEnrollment) {
                    throw new \Exception(
                        "No se encontró enrollment activo para el estudiante {$student->codstudent}"
                    );
                }

                $reflection = new \ReflectionMethod(StudentController::class, 'generateCarnetImageQR');
                $reflection->setAccessible(true);

                $carnetPath = $reflection->invoke($studentController, $studentWithRelations);

                $studentWithRelations->carnet = $carnetPath;
                $studentWithRelations->save();
            });

            $this->recordSuccess();

        } catch (\Throwable $e) {

            Log::error('ImportStudentsJob error', [
                'identify' => $identify,
                'batch'    => $this->batchId,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $this->recordError("Error procesando DNI {$identify}: " . $e->getMessage());
        }
    }

    /*
    ─────────────────────────────────────────
    HELPERS CACHE BATCH
    ─────────────────────────────────────────
    */

    private function recordSuccess(): void
    {
        $this->updateBatch(function (array &$state) {
            $state['processed']++;
            $state['success']++;
            $this->checkDone($state);
        });
    }

    private function recordSkipped(string $msg): void
    {
        $this->updateBatch(function (array &$state) use ($msg) {
            $state['processed']++;
            $state['skipped']++;
            $state['errors'][] = "[OMITIDO] {$msg}";
            $this->checkDone($state);
        });
    }

    private function recordError(string $msg): void
    {
        $this->updateBatch(function (array &$state) use ($msg) {
            $state['processed']++;
            $state['errors'][] = "[ERROR] {$msg}";
            $this->checkDone($state);
        });
    }

    private function checkDone(array &$state): void
    {
        if ($state['processed'] >= $state['total']) {
            $state['status'] = 'done';
        }
    }

    private function updateBatch(callable $callback): void
    {
        $lock = Cache::lock("import_batch_lock:{$this->batchId}", 5);
        $lock->block(10);

        try {
            $state = Cache::get("import_batch:{$this->batchId}", [
                'total'     => 0,
                'processed' => 0,
                'success'   => 0,
                'skipped'   => 0,
                'errors'    => [],
                'status'    => 'processing',
            ]);

            $callback($state);

            Cache::put("import_batch:{$this->batchId}", $state, now()->addHours(24));
        } finally {
            $lock->release();
        }
    }

    public function failed(\Throwable $exception): void
    {
        $identify = $this->row['identify_number'] ?? 'desconocido';

        $this->recordError(
            "Fallo definitivo DNI {$identify}: " . $exception->getMessage()
        );
    }

    /*
    ─────────────────────────────────────────
    HELPERS GENERALES
    ─────────────────────────────────────────
    */

    /**
     * Limpia un string para comparación:
     * - Quita espacios al inicio/final
     * - Colapsa múltiples espacios internos a uno solo
     * - NO toca mayúsculas/minúsculas ni caracteres especiales (ñ, tildes)
     *   El ILIKE de PostgreSQL hace la comparación case-insensitive correctamente.
     *
     * Ejemplo: "  Grupo 3  años   " → "Grupo 3 años"
     */
    private function normalizeString(string $value): string
    {
        // mb_strtolower respeta ñ, tildes y demás caracteres UTF-8
        // "Grupo 3 años  " → "grupo 3 años"
        // "GRUPO 3 AñOS  " → "grupo 3 años"   ← el strtoupper roto también queda bien
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)), 'UTF-8');
    }
}