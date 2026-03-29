<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Person;
use App\Models\System\Student;
use App\Models\System\GradeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\System\Assignee;
use App\Models\System\Assistance;
use App\Models\System\AssistanceSession;
use App\Models\System\Schedules;
use App\Models\System\Enrollment;
use App\Models\System\Period;
use Carbon\Carbon;
use App\Exports\AssistanceExport;
use App\Models\System\Justification;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AssistanceController extends Controller
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
            'title'       => 'Asistencias',
            'title_form'  => 'Asistencia',
            'controller'  => 'assistance',
            'totalRecord' => Assistance::count(),
        ];
        $this->keyword = null;
    }

    /* =============================================
       INDEX
    ============================================= */
    public function index()
    {
        $data = Assistance::with(
            'enrollment.student.person',
            'enrollment.grade_schedule.grade',
            'assistance_session.schedule'
        )
            ->orderBy('codassistance', 'DESC')
            ->limit($this->perPage)
            ->get();

        $schedules = Schedules::get();
        $opening   = AssistanceSession::whereNull('time_ending')->first();
        $periods = Period::get();

        $sessions = AssistanceSession::with('schedule')
            ->orderBy('date', 'DESC')
            ->orderBy('codassistance_session', 'DESC')
            ->get();

        // Grados con su nivel para el filtro
        $activeGradeIds = Enrollment::whereHas('period', fn($q) => $q->where('is_active', 'Y'))
            ->join('system.grade_schedule', 'system.enrollment.codgrade_schedule', '=', 'system.grade_schedule.codgrade_schedule')
            ->pluck('system.grade_schedule.codgrade')
            ->unique();

        $grades = Grade::whereIn('codgrade', $activeGradeIds)
            ->with('level')
            ->orderBy('name_large')
            ->get();

        return view('assistance.list', [
            'extend'    => $this->extend,
            'data'      => $data,
            'schedules' => $schedules,
            'opening'   => $opening,
            'sessions'  => $sessions,
            'grades'    => $grades,
            'periods' => $periods,

        ]);
    }

    /* =============================================
       RECORDS (paginación + filtros)
    ============================================= */
    public function records($from, $to, $keyword = null)
    {
        $sessionId       = request()->query('session');
        $gradeId         = request()->query('grade');
        $gradeScheduleId = request()->query('grade_schedule');

        $query = Assistance::with(
            'enrollment.student.person',
            'enrollment.grade_schedule.grade',
            'enrollment.grade_schedule.schedule',
            'enrollment.period',
            'assistance_session.schedule'
        )->orderBy('codassistance', 'DESC');

        // Filtro sesión
        if (!empty($sessionId) && $sessionId !== 'null') {
            $query->where('codassistance_session', $sessionId);
        }

        // Filtro grado/sección
        if (!empty($gradeScheduleId) && $gradeScheduleId !== 'null') {
            $query->whereHas(
                'enrollment',
                fn($q) => $q->where('codgrade_schedule', $gradeScheduleId)
            );
        } elseif (!empty($gradeId) && $gradeId !== 'null') {
            $query->whereHas(
                'enrollment.grade_schedule',
                fn($q) => $q->where('codgrade', $gradeId)
            );
        }

        // Búsqueda
        if (!empty($keyword) && $keyword !== 'null') {
            $query->whereHas(
                'enrollment.student.person',
                fn($p) => $p->where('firstname', 'ILIKE', "%{$keyword}%")
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
       AUSENTES
    ============================================= */
    public function absents($from, $to, $keyword = null)
    {
        $sessionId       = request()->query('session');
        $gradeId         = request()->query('grade');
        $gradeScheduleId = request()->query('grade_schedule');
        $period          = request()->query('codperiod');

        if (!$sessionId) {
            return response()->json(['success' => false, 'message' => 'Se requiere sesión'], 422);
        }

        $session = AssistanceSession::find($sessionId);
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesión no encontrada'], 404);
        }

        // Buscar enrollments activos del período activo, del horario de la sesión
        // que NO tengan asistencia en esta sesión
        $query = Enrollment::with('student.person', 'grade_schedule.grade')
            ->whereHas('period', fn($q) => $q->where('is_active', 'Y'))
            ->whereHas('grade_schedule', fn($q) => $q->where('codschedule', $session->codschedule))
            ->whereNotExists(function ($q) use ($sessionId) {
                $q->select(DB::raw(1))
                    ->from('system.assistance')
                    ->whereColumn('assistance.codenrollment', 'system.enrollment.codenrollment')
                    ->where('assistance.codassistance_session', $sessionId)
                    ->whereNull('assistance.deleted_at');
            })
            ->orderBy('codenrollment');

        if (!empty($gradeScheduleId)) {
            $query->where('codgrade_schedule', $gradeScheduleId);
        } elseif (!empty($gradeId)) {
            $query->whereHas('grade_schedule', fn($q) => $q->where('codgrade', $gradeId));
        }

        if (!empty($period)) {
            $query->where('codperiod', $period);
        }

        if (!empty($keyword) && $keyword !== 'null') {
            $query->whereHas('student.person', function ($p) use ($keyword) {
                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();
        $data  = $query->skip($from)->take($to - $from)->get();

        return response()->json(['success' => true, 'data' => $data, 'total' => $total, 'from' => $from, 'to' => $to]);
    }
    /* =============================================
       OPENING / CLOSING
    ============================================= */
    public function opening(Request $request)
    {
        $request->validate(['codschedule' => 'required']);

        $sessionOpen = AssistanceSession::whereNull('time_ending')->exists();
        if ($sessionOpen) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una sesión abierta. Ciérrala antes de aperturar una nueva.'
            ], 422);
        }

        $session = AssistanceSession::create([
            'codschedule'  => $request->codschedule,
            'date'         => Carbon::today(),
            'time_opening' => Carbon::now(),
            'time_ending'  => null,
        ]);

        // 🔥 PRECARGAR JUSTIFICADOS
        $this->loadJustifiedStudents($session);

        return response()->json(['success' => true, 'message' => 'Sesión aperturada']);
    }

    private function loadJustifiedStudents($session)
    {
        $enrollments = Enrollment::whereHas('grade_schedule', function ($q) use ($session) {
            $q->where('codschedule', $session->codschedule);
        })->get();

        foreach ($enrollments as $enrollment) {

            // Verificar si tiene justificación
            $justified = Justification::where('codenrollment', $enrollment->codenrollment)
                ->where(function ($q) use ($session) {
                    $q->where('type', 'JI') // indefinida
                        ->orWhere(function ($q2) use ($session) {
                            $q2->where('type', 'JT')
                                ->where('codassistance_session', $session->codassistance_session);
                        });
                })
                ->exists();

            if (!$justified) continue;

            // Evitar duplicados
            $exists = Assistance::where('codassistance_session', $session->codassistance_session)
                ->where('codenrollment', $enrollment->codenrollment)
                ->exists();

            if ($exists) continue;

            Assistance::create([
                'codassistance_session' => $session->codassistance_session,
                'codenrollment'         => $enrollment->codenrollment,
                'time_entry'            => now(),
                'status'                => 'justified',
                'observation'           => 'Justificación automática',
            ]);
        }
    }

    public function closing()
    {
        $session = AssistanceSession::whereNull('time_ending')->first();
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No hay ninguna sesión abierta.'
            ], 422);
        }

        $session->update(['time_ending' => Carbon::now()]);
        return response()->json(['success' => true, 'message' => 'Sesión cerrada correctamente']);
    }

    /* =============================================
       TAKE (vista QR)
    ============================================= */
    public function take()
    {
        $this->extend['controller'] = 'assistance-take';
        return view('assistance.take', ['extend' => $this->extend]);
    }


    /* =============================================
       VALIDATE ATTENDANCE (QR / manual)
    ============================================= */
    public function validateAttendance(Request $request)
    {
        $data = $request->validate([
            'dni'         => ['required', 'string', 'max:20'],
            'late'        => ['nullable', 'string'],
            'early'       => ['nullable', 'string'],
            'observation' => ['nullable', 'string', 'max:200'],
        ]);

        $dniStudent  = trim($data['dni']);
        $isLate      = ($data['late'] ?? null) === 'late';
        $isEarly = ($data['early'] ?? null) === 'early';
        $observation = $data['observation'] ?? null;

        // Buscar estudiante con su gradeSchedule para validar horario
        $enrollment = Enrollment::with('student.person', 'grade_schedule.schedule', 'period')
            ->whereHas('student.person', fn($q) => $q->where('identify_number', $dniStudent))
            ->whereHas('period', fn($q) => $q->where('is_active', 'Y'))
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado o sin matrícula activa'
            ], 404);
        }

        // Sesión activa
        $session = AssistanceSession::with('schedule')
            ->whereDate('date', Carbon::today())
            ->whereNull('time_ending')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No existe sesión activa hoy'
            ], 422);
        }

        // Verificar que el horario de la sesión coincida con el del estudiante
        if ($enrollment->grade_schedule && $enrollment->grade_schedule->codschedule !== $session->codschedule) {
            return response()->json([
                'success' => false,
                'message' => 'El estudiante no pertenece al horario de esta sesión'
            ], 422);
        }

        return DB::transaction(function () use ($enrollment, $session, $isLate, $isEarly, $observation) {

            $exists = Assistance::where('codassistance_session', $session->codassistance_session)
                ->where('codenrollment', $enrollment->codenrollment)
                ->lockForUpdate()
                ->first();

            if ($exists) {
                return response()->json([
                    'success'            => true,
                    'already_registered' => true,
                    'message'            => 'Asistencia ya registrada',
                    'status'             => $exists->status,
                    'time'               => $exists->time_entry,
                ]);
            }

            $now       = Carbon::now();
            $timeStart = Carbon::parse($session->schedule->time_start);
            $timeEnd   = Carbon::parse($session->schedule->time_end);
            $withinSchedule = $now->between($timeStart, $timeEnd);

            // NUEVO: si es "temprano", forzar time_entry = time_end - 1 minuto
            if ($isEarly) {
                $entryTime = $timeEnd->copy()->subMinute();
                $status    = 'present';
                $obs       = $observation ?: 'Registro posterior (llegó temprano)';
            } elseif (!$withinSchedule && !$isLate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fuera del horario permitido. Active "Registrar tardanza".'
                ], 422);
            } else {
                $entryTime = $now;
                $status    = ($isLate || !$withinSchedule) ? 'late' : 'present';
                $obs       = $status === 'late' ? ($observation ?: 'Tardanza') : null;
            }

            $attendance = Assistance::create([
                'codassistance_session' => $session->codassistance_session,
                'coduser_responsible'         => Auth::user()->coduser,
                'codenrollment'         => $enrollment->codenrollment,
                'time_entry'            => $entryTime,   // <-- usa la variable
                'status'                => $status,
                'observation'           => $obs,
            ]);

            return response()->json([
                'success'     => true,
                'message'     => $isEarly ? 'Asistencia registrada (llegó temprano)' : ($status === 'late' ? 'Tardanza registrada' : 'Asistencia registrada'),
                'status'      => $status,
                'time'        => $attendance->time_entry,
                'observation' => $attendance->observation,
            ]);
        });
    }

    /* =============================================
       DESTROY
    ============================================= */
    public function destroy($id)
    {
        try {
            Assistance::findOrFail($id)->delete();
            return response()->json([
                'success'      => true,
                'message'      => 'Registro eliminado correctamente',
                'totalRecords' => Assistance::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =============================================
       EXPORT EXCEL
    ============================================= */
    public function exportExcel(Request $request)
    {
        $sessionId       = $request->query('session');
        $gradeId         = $request->query('grade');
        $keyword         = $request->query('keyword');
        $gradeScheduleId = $request->query('grade_schedule');
        $tab             = $request->query('tab', 'present');
        $periodId         = $request->query('period');

        return Excel::download(
            new AssistanceExport($sessionId, $gradeId, $keyword, $gradeScheduleId, $tab, $periodId),
            'asistencias_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /* =============================================
       EXPORT PDF
    ============================================= */
    public function exportPdf(Request $request)
    {
        $sessionId       = $request->query('session');
        $gradeId         = $request->query('grade');
        $keyword         = $request->query('keyword');
        $periodId        = $request->query('period');
        $gradeScheduleId = $request->query('grade_schedule');

        $query = Assistance::with(
            'enrollment.student.person',
            'enrollment.grade_schedule.grade',
            'enrollment.grade_schedule.schedule',
            'assistance_session.schedule'
        )->orderBy('codassistance', 'DESC');

        if (!empty($periodId)) {
            $query->whereHas('enrollment', function ($q) use ($periodId) {
                $q->where('codperiod', $periodId);
            });
        }

        $periodName = null;

        if (!empty($periodId)) {
            $period = Period::find($periodId);
            $periodName = $period?->name;
        }

        if (!empty($sessionId)) {
            $query->where('codassistance_session', $sessionId);
        }

        if (!empty($gradeScheduleId)) {
            $query->whereHas('enrollment', fn($q) => $q->where('codgrade_schedule', $gradeScheduleId));
        } elseif (!empty($gradeId)) {
            $query->whereHas('enrollment.grade_schedule', fn($q) => $q->where('codgrade', $gradeId));
        }

        if (!empty($keyword)) {
            $query->whereHas('student.person', function ($p) use ($keyword) {
                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%");
            });
        }

        $data        = $query->get();
        $sessionInfo = !empty($sessionId)
            ? AssistanceSession::with('schedule')->find($sessionId)
            : null;
        $gradeInfo   = !empty($gradeId)
            ? Grade::with('level')->find($gradeId)
            : null;

        // Sección
        $sectionInfo = null;
        if (!empty($gradeScheduleId)) {
            $gs          = GradeSchedule::find($gradeScheduleId);
            $sectionInfo = $gs ? 'Sección ' . $gs->section : null;
        }

        // Ausentes
        $absents = collect();
        if ($sessionInfo) {
            // DESPUÉS — usar Enrollment en lugar de Student
            $presentEnrollmentIds = $data->pluck('codenrollment')->toArray();

            $absentQuery = Enrollment::with('student.person', 'grade_schedule.grade')
                ->whereHas('period', fn($q) => $q->where('is_active', 'Y'))
                ->whereHas('grade_schedule', fn($q) => $q->where('codschedule', $sessionInfo->codschedule))
                ->whereNotIn('codenrollment', $presentEnrollmentIds);

            if (!empty($gradeScheduleId)) {
                $absentQuery->where('codgrade_schedule', $gradeScheduleId);
            } elseif (!empty($gradeId)) {
                $absentQuery->whereHas('grade_schedule', fn($q) => $q->where('codgrade', $gradeId));
            }

            $absents = $absentQuery->get();
        }

        // Logo base64 — declarado antes del compact
        $logoPath   = public_path('img/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $logoPathSchool   = public_path('img/logo_school.png');
        $logoSchoolBase64 = file_exists($logoPathSchool)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPathSchool))
            : null;


        // Fuentes en base64
        // Fuentes base64 ← NUEVO
        $fontRegular   = public_path('fonts/MonaSans-Regular.ttf');
        $fontBold      = public_path('fonts/MonaSans-Bold.ttf');
        $fontExtraBold = public_path('fonts/MonaSans-ExtraBold.ttf');

        $fontRegularB64   = file_exists($fontRegular)   ? base64_encode(file_get_contents($fontRegular))   : null;
        $fontBoldB64      = file_exists($fontBold)       ? base64_encode(file_get_contents($fontBold))       : null;
        $fontExtraBoldB64 = file_exists($fontExtraBold) ? base64_encode(file_get_contents($fontExtraBold)) : null;

        $pdf = Pdf::loadView('assistance.export-pdf', compact(
            'data',
            'sessionInfo',
            'periodName',
            'gradeInfo',
            'sectionInfo',
            'logoBase64',
            'logoSchoolBase64',
            'absents',
            'fontRegularB64',
            'fontBoldB64',
            'fontExtraBoldB64'
        ))->setPaper('a4', 'portrait');

        // Construir nombre descriptivo del archivo
        $nameParts = ['ASISTENCIAS'];

        if (!empty($periodId) && $periodName) {
            $nameParts[] = str($periodName)->slug('_')->upper();
        }
        if (!empty($gradeId) && $gradeInfo) {
            $nameParts[] = str($gradeInfo->name_large)->slug('_')->upper();
        }
        if ($sectionInfo) {
            $nameParts[] = str($sectionInfo)->slug('_')->upper();
        }
        if (!empty($sessionId) && $sessionInfo) {
            $nameParts[] = 'SES_' . now()->parse($sessionInfo->date ?? now())->format('Ymd');
        }

        $filename = implode('_', $nameParts) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
    /* =============================================
       VALIDATE ATTENDANCE EXTERNAL (web view)
    ============================================= */
    public function validateAttendanceExternal($dni, $late = null)
    {
        $dniStudent = $dni;
        $late       = $late === 'late';

        if (!$dniStudent) {
            return view('assistance.validate', [
                'extend'  => $this->extend,
                'success' => false,
                'message' => 'Código no enviado',
                'data'    => null
            ]);
        }

        $enrollment = Enrollment::with('grade_schedule.schedule', 'student.person', 'period')
            ->whereHas('student.person', fn($q) => $q->where('identify_number', $dniStudent))
            ->whereHas('period', fn($q) => $q->where('is_active', 'Y'))
            ->first();

        if (!$enrollment) {
            return view('assistance.validate', [
                'extend'  => $this->extend,
                'success' => false,
                'message' => 'Estudiante no encontrado',
                'data'    => null
            ]);
        }

        $assistanceSession = AssistanceSession::with('schedule')
            ->whereDate('date', Carbon::today())
            ->whereNull('time_ending')
            ->first();

        if (!$assistanceSession) {
            return view('assistance.validate', [
                'extend'  => $this->extend,
                'success' => false,
                'message' => 'No existe sesión activa hoy',
                'data'    => null
            ]);
        }

        $now = Carbon::now()->format('H:i:s');
        if (
            $assistanceSession->schedule->time_start > $now ||
            $assistanceSession->schedule->time_end < $now
        ) {
            if (!$late) {
                return view('assistance.validate', [
                    'extend'  => $this->extend,
                    'success' => false,
                    'message' => 'Fuera del horario permitido',
                    'data'    => null
                ]);
            }
        }

        $exists = Assistance::where('codassistance_session', $assistanceSession->codassistance_session)
            ->where('codenrollment', $enrollment->codenrollment)
            ->exists();

        if ($exists) {
            return view('assistance.validate', [
                'extend'  => $this->extend,
                'success' => true,
                'message' => 'Asistencia ya registrada',
                'data'    => null
            ]);
        }

        $attendance = Assistance::create([
            'codassistance_session' => $assistanceSession->codassistance_session,
            'codenrollment'            => $enrollment->codenrollment,
            'time_entry'            => Carbon::now(),
            'status'                => $late ? 'late' : 'present',
            'observation'           => $late ? 'Registro tardío' : null,
        ]);

        return view('assistance.validate', [
            'extend'  => $this->extend,
            'success' => true,
            'message' => 'Asistencia registrada correctamente',
            'data'    => $attendance
        ]);
    }
}
