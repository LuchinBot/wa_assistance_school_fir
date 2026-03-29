<?php

namespace App\Http\Controllers;

use App\Jobs\ImportStudentsJob;
use App\Models\System\ImportBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportStudentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('module.permission:crear');
    }

    /**
     * Vista del formulario de importación
     */
    public function form()
    {
        return view('student.import', [
            'extend' => [
                'title'       => 'Importar Estudiantes',
                'title_form'  => 'Importación masiva',
                'view'        => 'import',
                'controller'  => 'student',
            ]
        ]);
    }

    /**
     * Recibe el Excel y despacha los jobs
     *
     * Formato Excel soportado (ambos casos):
     *
     * CASO A (5 columnas - con level, sin datos personales):
     *   level | grade | section | type_document | identify_number
     *
     * CASO B (10 columnas - completo):
     *   level | grade | section | type_document | identify_number |
     *   lastname_father | lastname_mom | firstname | gender | birthday
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        // Leer el Excel en memoria
        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Quitar cabecera
        $header = array_shift($rows);

        // Filtrar filas vacías y normalizar
        $students = [];
        foreach ($rows as $row) {
            // ── Columnas básicas ──
            // Índices:
            //   0 → level
            //   1 → grade
            //   2 → section
            //   3 → type_document
            //   4 → identify_number
            //   5 → lastname_father  (opcional, Caso B)
            //   6 → lastname_mom     (opcional, Caso B)
            //   7 → firstname        (opcional, Caso B)
            //   8 → gender           (opcional, Caso B)
            //   9 → birthday         (opcional, Caso B)
            $level          = $this->normalizeString($row[0] ?? '');
            $grade          = $this->normalizeString($row[1] ?? '');
            $section        = $this->normalizeString($row[2] ?? '');
            $typeDocument   = strtoupper(trim($row[3] ?? ''));
            $identifyNumber = trim($row[4] ?? '');

            // Si no hay DNI, saltar
            if (empty($identifyNumber)) {
                continue;
            }

            // Si type_document no es DNI (extranjero), saltar
            if ($typeDocument !== 'DNI') {
                continue;
            }

            // DNI debe tener 8 dígitos
            if (!preg_match('/^\d{8}$/', $identifyNumber)) {
                continue;
            }

            if (empty($level) || empty($grade) || empty($section)) {
                continue;
            }

            // ── Columnas extendidas (opcionales, Caso B) ──
            $lastnameFather = $this->toTitleCase($row[5] ?? null);
            $lastnameMom    = $this->toTitleCase($row[6] ?? null);
            $firstname      = $this->toTitleCase($row[7] ?? null);
            $genderRaw      = trim($row[8] ?? '');
            $birthdayRaw    = trim($row[9] ?? '');

            // Convertir género textual → código numérico
            $gender = null;
            if (!empty($genderRaw)) {
                $gender = (strtoupper($genderRaw) === 'MUJER') ? 2 : 1;
            }

            // Normalizar fecha: acepta d/m/Y o cualquier formato reconocido por strtotime
            $birthday = null;
            if (!empty($birthdayRaw)) {
                // Intentar primero d/m/Y (formato peruano habitual)
                $parsed = \DateTime::createFromFormat('d/m/Y', $birthdayRaw);
                if ($parsed) {
                    $birthday = $parsed->format('Y-m-d');
                } else {
                    // Fallback genérico
                    $ts = strtotime($birthdayRaw);
                    if ($ts !== false) {
                        $birthday = date('Y-m-d', $ts);
                    }
                }
            }

            $students[] = [
                'level'           => $level,
                'grade'           => $grade,
                'section'         => $section,
                'type_document'   => $typeDocument,
                'identify_number' => $identifyNumber,

                // Datos manuales (null si el Excel no los trae → Caso A)
                'manual_firstname'       => $firstname,
                'manual_lastname_father' => $lastnameFather,
                'manual_lastname_mom'    => $lastnameMom,
                'manual_gender'          => $gender,
                'manual_birthday'        => $birthday,
            ];
        }

        if (empty($students)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron registros válidos con DNI en el archivo.'
            ], 422);
        }

        // Crear un ID de lote único para tracking
        $batchId = Str::uuid()->toString();

        // Guardar estado inicial en cache (TTL 24h)
        Cache::put("import_batch:{$batchId}", [
            'total'      => count($students),
            'processed'  => 0,
            'success'    => 0,
            'skipped'    => 0,
            'errors'     => [],
            'status'     => 'processing',
            'started_at' => now()->toIso8601String(),
        ], now()->addHours(24));

        // Despachar un job por cada estudiante
        foreach ($students as $index => $student) {
            ImportStudentsJob::dispatch($student, $batchId)
                ->onQueue('imports')
                ->delay(now()->addSeconds((int) ($index * 0.5)));
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Importación iniciada. Se procesarán ' . count($students) . ' estudiantes.',
            'batch_id' => $batchId,
            'total'    => count($students),
        ]);
    }

    /**
     * Polling: devuelve el estado del lote
     */
    public function status(string $batchId)
    {
        $state = Cache::get("import_batch:{$batchId}");

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'Lote no encontrado o expirado.'
            ], 404);
        }

        $percent = $state['total'] > 0
            ? round(($state['processed'] / $state['total']) * 100)
            : 0;

        return response()->json([
            'success'       => true,
            'status'        => $state['status'],
            'total'         => $state['total'],
            'processed'     => $state['processed'],
            'success_count' => $state['success'],
            'skipped'       => $state['skipped'],
            'errors'        => array_slice($state['errors'], -20),
            'percent'       => $percent,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────

    /**
     * Limpia un string para comparación:
     * - Quita espacios al inicio/final
     * - Colapsa múltiples espacios internos a uno solo
     * - Preserva mayúsculas/minúsculas y caracteres especiales (ñ, tildes)
     *
     * Ejemplo: "  Grupo 3 años   " → "Grupo 3 años"
     */
    private function normalizeString(?string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value ?? '')), 'UTF-8');
    }

    /**
     * Convierte una cadena en mayúsculas a Title Case.
     * Devuelve null si la cadena está vacía.
     *
     * Ejemplo: "MYA ALESSANDRA" → "Mya Alessandra"
     */
    private function toTitleCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}