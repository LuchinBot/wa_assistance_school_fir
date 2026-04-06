<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\System\Assistance;
use App\Models\System\AssistanceSession;
use App\Models\System\Enrollment;
use App\Models\System\GradeSchedule;
use App\Models\System\Period;
use App\Models\System\Schedules;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class WeekingReportController extends Controller
{
    public $extend = null;
    protected $perPage = 15;

    public function __construct()
    {
        //$this->middleware('module.permission:listar')->only('index');

        $this->extend = [
            'title'       => 'Reporte Semanal',
            'title_form'  => 'reporte semanal',
            'view'        => 'list',
            'controller'  => 'weekingreport',
            'totalRecord' => 0,
        ];
    }

    /* =============================================
       INDEX
    ============================================= */
    public function index()
    {
        $schedules = Schedules::orderBy('turn')->get();
        $periods   = Period::orderByDesc('codperiod')->get();

        // Grados activos con su nivel
        $activeGradeIds = Enrollment::whereHas('period', fn($q) => $q->where('is_active', 'Y'))
            ->join('system.grade_schedule', 'system.enrollment.codgrade_schedule', '=', 'system.grade_schedule.codgrade_schedule')
            ->pluck('system.grade_schedule.codgrade')
            ->unique();

        $grades = Grade::whereIn('codgrade', $activeGradeIds)
            ->with('level')
            ->orderBy('name_large')
            ->get();

        // Fechas por defecto: lunes y viernes de la semana actual
        $defaultFrom = Carbon::now()->startOfWeek()->format('Y-m-d');
        $defaultTo   = Carbon::now()->endOfWeek()->subDays(2)->format('Y-m-d'); // viernes

        return view('report.weeking.list', [
            'extend'      => $this->extend,
            'schedules'   => $schedules,
            'periods'     => $periods,
            'grades'      => $grades,
            'defaultFrom' => $defaultFrom,
            'defaultTo'   => $defaultTo,
        ]);
    }

    /* =============================================
       RECORDS — detalle paginado
    ============================================= */
    public function records(Request $request, $from = 0, $to = 15, $keyword = null)
    {
        $from    = (int) $from;
        $to      = (int) $to;
        $keyword = ($keyword === 'null') ? null : $keyword;

        $dateFrom        = $request->query('date_from');
        $dateTo          = $request->query('date_to');
        $codschedule     = $request->query('codschedule');
        $gradeId         = $request->query('grade');
        $gradeScheduleId = $request->query('grade_schedule');
        $periodId        = $request->query('codperiod');

        $query = Assistance::with(
            'enrollment.student.person',
            'enrollment.grade_schedule.grade',
            'enrollment.grade_schedule.schedule',
            'enrollment.period',
            'assistance_session.schedule'
        )->orderBy('codassistance', 'DESC');

        if (!empty($dateFrom)) {
            $query->whereHas('assistance_session', fn($q) => $q->whereDate('date', '>=', $dateFrom));
        }
        if (!empty($dateTo)) {
            $query->whereHas('assistance_session', fn($q) => $q->whereDate('date', '<=', $dateTo));
        }
        if (!empty($codschedule) && $codschedule !== 'null') {
            $query->whereHas('assistance_session', fn($q) => $q->where('codschedule', $codschedule));
        }
        if (!empty($periodId) && $periodId !== 'null') {
            $query->whereHas('enrollment', fn($q) => $q->where('codperiod', $periodId));
        }
        if (!empty($gradeScheduleId) && $gradeScheduleId !== 'null') {
            $query->whereHas('enrollment', fn($q) => $q->where('codgrade_schedule', $gradeScheduleId));
        } elseif (!empty($gradeId) && $gradeId !== 'null') {
            $query->whereHas('enrollment.grade_schedule', fn($q) => $q->where('codgrade', $gradeId));
        }
        if (!empty($keyword)) {
            $query->whereHas(
                'enrollment.student.person',
                fn($p) =>
                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%")
            );
        }

        $total = (clone $query)->count();
        $data  = $query->skip($from)->take($to - $from)->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'total'   => $total,
            'from'    => $from,
            'to'      => $to,
        ]);
    }

    /* =============================================
       SUMMARY — resumen por estudiante
    ============================================= */
    public function summary(Request $request, $from = 0, $to = 15, $keyword = null)
    {
        $from    = (int) $from;
        $to      = (int) $to;
        $keyword = ($keyword === 'null') ? null : $keyword;

        $dateFrom        = $request->query('date_from');
        $dateTo          = $request->query('date_to');
        $codschedule     = $request->query('codschedule');
        $gradeId         = $request->query('grade');
        $gradeScheduleId = $request->query('grade_schedule');
        $periodId        = $request->query('codperiod');

        // Sesiones del rango
        $sessionQuery = AssistanceSession::query();
        if (!empty($dateFrom))    $sessionQuery->whereDate('date', '>=', $dateFrom);
        if (!empty($dateTo))      $sessionQuery->whereDate('date', '<=', $dateTo);
        if (!empty($codschedule) && $codschedule !== 'null') {
            $sessionQuery->where('codschedule', $codschedule);
        }
        $sessionIds = $sessionQuery->pluck('codassistance_session');
        $totalDays  = $sessionIds->count();

        // Enrollments base
        $enrollQuery = Enrollment::with('student.person', 'grade_schedule.grade', 'grade_schedule.schedule', 'period')
            ->whereHas(
                'period',
                fn($q) => !empty($periodId) && $periodId !== 'null'
                    ? $q->where('codperiod', $periodId)
                    : $q->where('is_active', 'Y')
            );

        if (!empty($codschedule) && $codschedule !== 'null') {
            $enrollQuery->whereHas('grade_schedule', fn($q) => $q->where('codschedule', $codschedule));
        }
        if (!empty($gradeScheduleId) && $gradeScheduleId !== 'null') {
            $enrollQuery->where('codgrade_schedule', $gradeScheduleId);
        } elseif (!empty($gradeId) && $gradeId !== 'null') {
            $enrollQuery->whereHas('grade_schedule', fn($q) => $q->where('codgrade', $gradeId));
        }
        if (!empty($keyword)) {
            $enrollQuery->whereHas(
                'student.person',
                fn($p) =>
                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%")
            );
        }

        $total = (clone $enrollQuery)->count();

        // Ordenar por apellido
        $enrollments = $enrollQuery
            ->join('system.student', 'system.enrollment.codstudent', '=', 'system.student.codstudent')
            ->join('person', 'system.student.codperson', '=', 'person.codperson')
            ->orderBy('person.lastname_father')
            ->orderBy('person.firstname')
            ->select('system.enrollment.*')
            ->skip($from)->take($to - $from)
            ->get();

        $enrollmentIds = $enrollments->pluck('codenrollment');

        $attendanceCounts = Assistance::whereIn('codassistance_session', $sessionIds)
            ->whereIn('codenrollment', $enrollmentIds)
            ->whereNull('deleted_at')
            ->select('codenrollment', 'status', DB::raw('count(*) as total'))
            ->groupBy('codenrollment', 'status')
            ->get()
            ->groupBy('codenrollment');

        $result = $enrollments->map(function ($enrollment) use ($attendanceCounts, $totalDays) {
            $counts    = $attendanceCounts->get($enrollment->codenrollment, collect());
            $present   = $counts->where('status', 'present')->sum('total');
            $late      = $counts->where('status', 'late')->sum('total');
            $justified = $counts->where('status', 'justified')->sum('total');
            $attended  = $present + $late + $justified;
            $absent    = max(0, $totalDays - $attended);

            return [
                'codenrollment'  => $enrollment->codenrollment,
                'student'        => $enrollment->student,
                'grade_schedule' => $enrollment->grade_schedule,
                'period'         => $enrollment->period,
                'present'        => $present,
                'late'           => $late,
                'justified'      => $justified,
                'absent'         => $absent,
                'total_days'     => $totalDays,
                'percentage'     => $totalDays > 0 ? round(($attended / $totalDays) * 100) : 0,
            ];
        });

        return response()->json([
            'success'    => true,
            'data'       => $result,
            'total'      => $total,
            'total_days' => $totalDays,
            'from'       => $from,
            'to'         => $to,
        ]);
    }

    /* =============================================
       EXPORT PDF
    ============================================= */
    public function exportPdf(Request $request)
    {
        $dateFrom        = $request->query('date_from');
        $dateTo          = $request->query('date_to');
        $codschedule     = $request->query('codschedule');
        $gradeId         = $request->query('grade');
        $gradeScheduleId = $request->query('grade_schedule');
        $periodId        = $request->query('codperiod');
        $keyword         = $request->query('keyword');

        // ── Sesiones del rango ──
        $sessionQuery = AssistanceSession::with('schedule');
        if (!empty($dateFrom)) $sessionQuery->whereDate('date', '>=', $dateFrom);
        if (!empty($dateTo))   $sessionQuery->whereDate('date', '<=', $dateTo);
        if (!empty($codschedule) && $codschedule !== 'null') {
            $sessionQuery->where('codschedule', $codschedule);
        }
        $sessions   = $sessionQuery->orderBy('date')->get();
        $sessionIds = $sessions->pluck('codassistance_session');
        $totalDays  = $sessionIds->count();

        // ── Enrollments ──
        $enrollQuery = Enrollment::with(
            'student.person',
            'grade_schedule.grade.level',
            'grade_schedule.schedule',
            'period'
        )->whereHas(
            'period',
            fn($q) => !empty($periodId) && $periodId !== 'null'
                ? $q->where('codperiod', $periodId)
                : $q->where('is_active', 'Y')
        );

        if (!empty($codschedule) && $codschedule !== 'null') {
            $enrollQuery->whereHas('grade_schedule', fn($q) => $q->where('codschedule', $codschedule));
        }
        if (!empty($gradeScheduleId) && $gradeScheduleId !== 'null') {
            $enrollQuery->where('codgrade_schedule', $gradeScheduleId);
        } elseif (!empty($gradeId) && $gradeId !== 'null') {
            $enrollQuery->whereHas('grade_schedule', fn($q) => $q->where('codgrade', $gradeId));
        }
        if (!empty($keyword) && $keyword !== 'null') {
            $enrollQuery->whereHas(
                'student.person',
                fn($p) =>
                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%")
            );
        }

        // Ordenar por grado → sección → apellido para que el groupBy quede limpio
        $enrollments = $enrollQuery
            ->join('system.grade_schedule as gs', 'system.enrollment.codgrade_schedule', '=', 'gs.codgrade_schedule')
            ->join('grade', 'gs.codgrade', '=', 'grade.codgrade')
            ->join('system.student', 'system.enrollment.codstudent', '=', 'system.student.codstudent')
            ->join('person', 'system.student.codperson', '=', 'person.codperson')
            ->orderBy('grade.name_large')
            ->orderBy('gs.section')
            ->orderBy('person.lastname_father')
            ->orderBy('person.firstname')
            ->select('system.enrollment.*')
            ->get();

        $enrollmentIds = $enrollments->pluck('codenrollment');

        // ── Asistencias agrupadas ──
        $attendanceCounts = Assistance::whereIn('codassistance_session', $sessionIds)
            ->whereIn('codenrollment', $enrollmentIds)
            ->whereNull('deleted_at')
            ->select('codenrollment', 'status', DB::raw('count(*) as total'))
            ->groupBy('codenrollment', 'status')
            ->get()
            ->groupBy('codenrollment');

        // ── Detalle diario ──
        $dailyAttendance = Assistance::whereIn('codassistance_session', $sessionIds)
            ->whereIn('codenrollment', $enrollmentIds)
            ->whereNull('deleted_at')
            ->select('codenrollment', 'codassistance_session', 'status', 'time_entry')
            ->get()
            ->groupBy('codenrollment')
            ->map(fn($items) => $items->keyBy('codassistance_session'));

        // ── Construir colección de estudiantes ──
        $students = $enrollments->map(function ($enrollment) use (
            $attendanceCounts,
            $totalDays,
            $dailyAttendance,
            $sessionIds
        ) {
            $counts    = $attendanceCounts->get($enrollment->codenrollment, collect());
            $present   = $counts->where('status', 'present')->sum('total');
            $late      = $counts->where('status', 'late')->sum('total');
            $justified = $counts->where('status', 'justified')->sum('total');
            $attended  = $present + $late + $justified;
            $absent    = max(0, $totalDays - $attended);

            // Estado por sesión (índice numérico = posición en $sessions)
            $daily = $sessionIds->map(function ($sid) use ($dailyAttendance, $enrollment) {
                $record = $dailyAttendance->get($enrollment->codenrollment)?->get($sid);
                return $record ? $record->status : 'absent';
            })->values();

            return [
                'person'         => $enrollment->student->person,
                'grade_schedule' => $enrollment->grade_schedule,
                'present'        => $present,
                'late'           => $late,
                'justified'      => $justified,
                'absent'         => $absent,
                'total_days'     => $totalDays,
                'percentage'     => $totalDays > 0 ? round(($attended / $totalDays) * 100) : 0,
                'daily'          => $daily,
                // Clave de agrupación para la vista (ya ordenado desde el query)
                '_group'         => ($enrollment->grade_schedule?->grade?->name_large ?? 'ZZZ')
                    . '||'
                    . ($enrollment->grade_schedule?->section ?? 'Z'),
            ];
        });

        // ── Info adicional ──
        $scheduleInfo = !empty($codschedule) && $codschedule !== 'null'
            ? Schedules::find($codschedule)
            : null;
        $gradeInfo    = !empty($gradeId) && $gradeId !== 'null'
            ? Grade::with('level')->find($gradeId)
            : null;
        $sectionInfo  = !empty($gradeScheduleId) && $gradeScheduleId !== 'null'
            ? GradeSchedule::find($gradeScheduleId)
            : null;
        $periodInfo   = !empty($periodId) && $periodId !== 'null'
            ? Period::find($periodId)
            : null;

        // ── Logos ──
        $logoBase64       = $this->encodeImage(public_path('img/logo.png'), 'png');
        $logoSchoolBase64 = $this->encodeImage(public_path('img/logo_school.png'), 'png');

        // ── Fuentes ──
        $fontRegularB64   = $this->encodeFont(public_path('fonts/MonaSans-Regular.ttf'));
        $fontBoldB64      = $this->encodeFont(public_path('fonts/MonaSans-Bold.ttf'));
        $fontExtraBoldB64 = $this->encodeFont(public_path('fonts/MonaSans-ExtraBold.ttf'));

        $pdf = Pdf::loadView('report.weeking.export_pdf', compact(
            'students',
            'sessions',
            'totalDays',
            'scheduleInfo',
            'gradeInfo',
            'sectionInfo',
            'periodInfo',
            'dateFrom',
            'dateTo',
            'logoBase64',
            'logoSchoolBase64',
            'fontRegularB64',
            'fontBoldB64',
            'fontExtraBoldB64'
        ))->setPaper('a4', 'landscape');

        // ── Nombre del archivo ──
        $parts = ['REPORTE_SEMANAL'];
        if ($periodInfo)   $parts[] = str($periodInfo->name)->slug('_')->upper();
        if ($gradeInfo)    $parts[] = str($gradeInfo->name_large)->slug('_')->upper();
        if ($sectionInfo)  $parts[] = 'SEC_' . strtoupper($sectionInfo->section);
        if ($dateFrom)     $parts[] = Carbon::parse($dateFrom)->format('dmY');
        if ($dateTo)       $parts[] = Carbon::parse($dateTo)->format('dmY');
        $filename = implode('_', $parts) . '.pdf';

        return $pdf->download($filename);
    }

    /* =============================================
       SECCIONES POR GRADO + HORARIO (AJAX)
    ============================================= */
    public function sectionsByGrade(Request $request)
    {
        $gradeId     = $request->query('grade');
        $codschedule = $request->query('schedule');

        $query = GradeSchedule::with('grade')
            ->where('codgrade', $gradeId);

        if (!empty($codschedule)) {
            $query->where('codschedule', $codschedule);
        }

        $sections = $query->orderBy('section')->get();

        return response()->json(['success' => true, 'data' => $sections]);
    }

    /* =============================================
       HELPERS PRIVADOS
    ============================================= */
    private function encodeImage(string $path, string $ext): ?string
    {
        if (!file_exists($path)) return null;
        return "data:image/{$ext};base64," . base64_encode(file_get_contents($path));
    }

    private function encodeFont(string $path): ?string
    {
        if (!file_exists($path)) return null;
        return base64_encode(file_get_contents($path));
    }
}
