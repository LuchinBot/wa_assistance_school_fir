<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Grade;
use App\Models\Person;
use App\Models\Profession;
use App\Models\System\Assignee;
use App\Models\System\GradeSchedule;
use App\Models\System\Student;
use App\Models\System\Media;
use App\Models\System\Enrollment;
use App\Models\System\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Carbon\Carbon;
use App\Exports\StudentExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    public $extend = null;
    public $keyword;
    protected $perPage = 10;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:editar')->only('form');
        $this->middleware('module.permission:crear')->only(['store']);
        $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title' => 'Estudiantes',
            'title_form' => 'Estudiante',
            'view' => 'list',
            'controller' => 'student',
            'totalRecord' => Student::count(),
        ];
        $this->keyword = null;
    }

    public function index()
    {
        $user = Auth::user();
        $period = Period::where('is_active', 'Y')->first();
        $periods = Period::get();

        $data = Student::with([
            'person',
            'currentEnrollment.grade_schedule.grade',
            'currentEnrollment.grade_schedule.schedule'
        ])
            ->whereHas('enrollments', function ($q) use ($period) {
                $q->where('codperiod', $period->codperiod);
            })
            ->orderByDesc('codstudent')
            ->limit($this->perPage)
            ->get();

        $grade_schedules = GradeSchedule::get();
        return view('student.list', [
            'extend' => $this->extend,
            'data' => $data,
            'grade_schedules' => $grade_schedules,
            'periods' => $periods,
        ]);
    }
    public function form(Request $request, $id = null)
    {
        $user = Auth::user();

        $students = $id
            ? Student::with([
                'person',
                'currentEnrollment.grade_schedule',
                'currentEnrollment.period',
            ])->findOrFail($id)
            : null;

        $persons = Person::orderByDesc('codperson')->get();
        $grade_schedules = GradeSchedule::orderByDesc('codgrade_schedule')->get();
        $assignees = Assignee::orderByDesc('codassignee')->get();
        $periods = Period::orderByDesc('codperiod')->get();

        $this->extend['view'] = 'form';

        return view('student.form', [
            'extend' => $this->extend,
            'students' => $students,
            'persons' => $persons,
            'grade_schedules' => $grade_schedules,
            'periods' => $periods,
            'assignees' => $assignees,
            'redirect' => $request->get('redirect')
        ]);
    }

    public function store(Request $request, $id = null)
    {
        $rules = [
            'codperson' => [
                'required',
                Rule::exists('person', 'codperson')
            ],
            'codgrade_schedule' => [
                'required',
                Rule::exists('grade_schedule', 'codgrade_schedule')
            ],
            'codperiod' => [
                'required',
                Rule::exists('period', 'codperiod')
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $data = $request->all();

            if ($id) {

                $student = Student::findOrFail($id);

                if ($student->carnet) {
                    Http::post(config('app.files_url') . '/api/delete', [
                        'path' => $student->carnet
                    ]);
                }

                $student->update([
                    'codperson'   => $request->codperson,
                    'codassignee' => $request->codassignee,
                ]);

                $enrollment = Enrollment::where('codstudent', $student->codstudent)
                    ->where('codperiod', $request->codperiod)
                    ->first();

                if ($enrollment) {

                    $enrollment->update([
                        'codgrade_schedule' => $request->codgrade_schedule
                    ]);
                } else {

                    Enrollment::create([
                        'codstudent'        => $student->codstudent,
                        'codgrade_schedule' => $request->codgrade_schedule,
                        'codperiod'         => $request->codperiod
                    ]);
                }
                $message = 'Registro actualizado exitosamente';
            } else {

                $student = Student::create([
                    'codperson'   => $request->codperson,
                    'codassignee' => $request->codassignee,
                ]);

                Enrollment::create([
                    'codstudent'        => $student->codstudent,
                    'codgrade_schedule' => $request->codgrade_schedule,
                    'codperiod'         => $request->codperiod,
                ]);
                $message = 'Registro creado exitosamente';
            }

            // Cargar relaciones necesarias
            $student->load([
                'person',
                'assignee',
                'currentEnrollment.grade_schedule.grade.level',
                'currentEnrollment.grade_schedule.schedule',
            ]);

            // Generar carnet
            $carnetPath = $this->generateCarnetImageQR($student);

            $student->update([
                'carnet' => $carnetPath
            ]);

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'data'         => $student,
                'carnet_url'   => config('app.files_url') . '/' . $carnetPath,
                'totalRecords' => Student::count(),
                'redirect'     => $request->redirect ?? route('student.list')
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error generando carnet de estudiante', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro'
            ], 500);
        }
    }

    public function records(Request $request, $from, $to, $keyword = null)
    {
        $periodId = $request->get('codperiod');
        $query = $query = Student::with([
            'person',
            'currentEnrollment.grade_schedule.grade.level',
            'currentEnrollment.grade_schedule.schedule'
        ])->orderBy('codstudent', 'DESC');

        // BUSCADOR
        if (!empty($keyword) && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('codstudent', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%")
                            ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        if ($periodId) {
            $query->whereHas('enrollments', function ($q) use ($periodId) {
                $q->where('codperiod', $periodId);
            });
        }
        // FILTRO POR GRADO + SECCION + TURNO
        if ($request->codgrade_schedule) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('codgrade_schedule', $request->codgrade_schedule);
            });
        }

        $total = (clone $query)->count();

        $data = $query
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'from' => $from,
            'to' => $to
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $user = Auth::user();

        $query = Student::with('person')
            ->orderBy('codstudent', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('codstudent', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%")
                            ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = (clone $query)->count();

        $data = $query->limit($this->perPage)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'keyword' => $keyword
        ]);
    }

    /**
     * Genera una imagen pequeña con solo el QR del DNI + el número de DNI debajo.
     *
     * Tamaño final: ~354x420px @ 300dpi ≈ 3cm x 3.5cm en impresión.
     * El QR ocupa 354x354px y el DNI se muestra en una franja blanca debajo.
     */
    private function generateCarnetImageQR(Student $student): string
    {
        if (!$student->person) {
            throw new \Exception('El estudiante no tiene persona asociada');
        }

        $dni = (string) $student->person->identify_number;

        /*
        ─────────────────────────────
        1. GENERAR QR
        ─────────────────────────────
        */
        $qrSize   = 354;   // px — a 300dpi ≈ 3cm
        $barHeight = 66;   // px franja blanca para el texto del DNI
        $padding  = 0;     // sin padding extra, el QR ya trae margin interno

        $qrCode = new QrCode(
            data: $dni,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $qrSize,
            margin: 8       // margen interno del QR en módulos
        );

        $writer   = new PngWriter();
        $qrResult = $writer->write($qrCode);

        // Guardar QR en temp
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $qrTempPath = $tempDir . '/qr_' . $student->codstudent . '_' . uniqid() . '.png';
        $qrResult->saveToFile($qrTempPath);

        /*
        ─────────────────────────────
        2. COMPONER IMAGEN FINAL
            - Ancho  = ancho real del QR generado
            - Alto   = alto QR + franja DNI
        ─────────────────────────────
        */
        $manager = new ImageManager(new Driver());
        $qrImg   = $manager->read($qrTempPath);

        $realW = $qrImg->width();   // normalmente == $qrSize
        $realH = $qrImg->height();

        // Lienzo blanco: QR arriba + franja de texto abajo
        $canvas = $manager->create($realW, $realH + $barHeight)
            ->fill('#ffffff');

        // Pegar QR en la parte superior
        $canvas->place($qrImg, 'top-left', 0, 0);

        // Escribir DNI centrado en la franja inferior
        $fontPath = resource_path('fonts/Roboto-Bold.ttf');
        $fontSize = 26;   // px — legible pero compacto

        $canvas->text(
            $dni,
            (int) ($realW / 2),                 // centrado horizontalmente
            $realH + (int) ($barHeight / 2) + 9, // centrado verticalmente en la franja
            function ($f) use ($fontPath, $fontSize) {
                $f->filename($fontPath);
                $f->size($fontSize);
                $f->color('#000000');
                $f->align('center');
                $f->valign('middle');
            }
        );

        /*
        ─────────────────────────────
        3. GUARDAR Y SUBIR
        ─────────────────────────────
        */
        $filename = $dni . '_carnet.jpg';
        $tempPath = storage_path('app/' . $filename);

        $canvas->save($tempPath, 95);   // calidad 95 para que el QR no pierda detalle

        // Limpiar temp del QR
        if (file_exists($qrTempPath)) {
            unlink($qrTempPath);
        }

        // Subir al servidor de archivos
        $response = Http::timeout(30)
            ->retry(3, 2000) // 3 intentos, espera 2 segundos entre cada uno
            ->withoutVerifying()
            ->attach(
                'file',
                fopen($tempPath, 'r'),
                $filename
            )
            ->post(
                config('app.files_url') . '/api/upload',
                ['folder' => 'carnets']
            );

        unlink($tempPath);

        if (!$response->successful()) {
            throw new \Exception('Error subiendo carné: ' . $response->body());
        }

        $data = $response->json();
        if (!$data || !isset($data['path'])) {
            throw new \Exception('Respuesta inválida del servidor: ' . $response->body());
        }

        return $data['path'];
    }

    private function generateQrTemp(Student $student): ?string
    {
        try {

            $qrData = $student->person->identify_number;

            $tempDir = storage_path('app/temp');

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempPath = $tempDir . '/qr_student_' . $student->codstudent . '.png';

            // ✅ Forma correcta v5 (usa enum)
            $qrCode = new QrCode(
                data: $qrData,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 350,
                margin: 10
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            $result->saveToFile($tempPath);

            return $tempPath;
        } catch (\Exception $e) {

            Log::error('Error generando QR', [
                'student_id' => $student->codstudent,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    private function downloadTempImage(string $url): string
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $tempPath  = $tempDir . '/img_' . uniqid() . '.' . $extension;

        $imageData = Http::get($url)->body();

        if (empty($imageData)) {
            throw new \Exception('No se pudo descargar la imagen desde: ' . $url);
        }

        file_put_contents($tempPath, $imageData);

        return $tempPath;
    }

    public function destroy($id)
    {
        try {

            $student = Student::findOrFail($id);

            if ($student->carnet) {

                $response = Http::post(
                    config('app.files_url') . '/api/delete',
                    [
                        'path' => $student->carnet
                    ]
                );
            }

            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Student::count()
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /* =============================================
       EXPORT EXCEL
    ============================================= */
    public function exportExcel(Request $request)
    {
        $gradeScheduleId = $request->query('grade_schedule');
        $keyword         = $request->query('keyword');
        $period         = $request->query('period');

        return Excel::download(
            new StudentExport($gradeScheduleId, $keyword, $period),
            'estudiantes_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $gradeScheduleId = $request->query('grade_schedule');
        $periodId        = $request->input('period');
        $keyword         = $request->query('keyword');

        $query = Student::with([
            'person',
            'currentEnrollment.grade_schedule.grade',
            'currentEnrollment.grade_schedule.schedule'
        ])->orderBy('codstudent', 'DESC');

        if (!empty($periodId)) {
            if (!empty($periodId)) {
                $query->whereHas('enrollments', function ($q) use ($periodId) {
                    $q->where('codperiod', $periodId);
                });
            }
        }

        $periodName = null;

        if (!empty($periodId)) {
            $period = Period::find($periodId);
            $periodName = $period?->name;
        }
        if (!empty($gradeScheduleId)) {
            $query->whereHas('currentEnrollment', function ($q) use ($gradeScheduleId) {
                $q->where('codgrade_schedule', $gradeScheduleId);
            });
        }

        if (!empty($keyword)) {
            $query->whereHas('person', function ($p) use ($keyword) {
                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
            });
        }

        $data = $query->get();

        $gradeScheduleInfo = null;
        if (!empty($gradeScheduleId)) {
            $gs = GradeSchedule::with('grade', 'schedule')->find($gradeScheduleId);
            $gradeScheduleInfo = $gs
                ? ($gs->grade?->name_large . ' — Sección ' . $gs->section . ' · ' . $gs->schedule?->turn)
                : null;
        }

        // Logos
        $logoPath         = public_path('img/logo.png');
        $logoBase64       = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $logoPathSchool   = public_path('img/logo_school.png');
        $logoSchoolBase64 = file_exists($logoPathSchool)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPathSchool))
            : null;

        // Fuentes
        $fontRegular   = public_path('fonts/MonaSans-Regular.ttf');
        $fontBold      = public_path('fonts/MonaSans-Bold.ttf');
        $fontExtraBold = public_path('fonts/MonaSans-ExtraBold.ttf');

        $fontRegularB64   = file_exists($fontRegular)   ? base64_encode(file_get_contents($fontRegular))   : null;
        $fontBoldB64      = file_exists($fontBold)       ? base64_encode(file_get_contents($fontBold))       : null;
        $fontExtraBoldB64 = file_exists($fontExtraBold)  ? base64_encode(file_get_contents($fontExtraBold)) : null;

        $pdf = Pdf::loadView('student.export-pdf', compact(
            'data',
            'gradeScheduleInfo',
            'periodName',
            'keyword',
            'logoBase64',
            'logoSchoolBase64',
            'fontRegularB64',
            'fontBoldB64',
            'fontExtraBoldB64'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('estudiantes_' . now()->format('Ymd_His') . '.pdf');
    }
}
