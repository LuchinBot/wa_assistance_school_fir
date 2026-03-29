<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestApiController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private function baseQuery()
    {
        return DB::table('system.assistance as a')
            ->join('system.assistance_session as s',  's.codassistance_session', '=', 'a.codassistance_session')
            ->join('system.enrollment as e',          'e.codenrollment',         '=', 'a.codenrollment')
            ->join('system.period as per',            'per.codperiod',           '=', 'e.codperiod')
            ->join('system.student as st',            'st.codstudent',           '=', 'e.codstudent')
            ->join('main.person as p',                'p.codperson',             '=', 'st.codperson')
            ->join('system.grade_schedule as gs',     'gs.codgrade_schedule',    '=', 'e.codgrade_schedule')
            ->join('main.grade as g',                 'g.codgrade',              '=', 'gs.codgrade')
            ->join('system.schedules as sc',          'sc.codschedule',          '=', 'gs.codschedule')
            ->whereNull('a.deleted_at')
            ->whereNull('e.deleted_at')
            ->whereNull('st.deleted_at')
            ->whereNull('gs.deleted_at');
    }

    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['period'])) {
            $query->where('e.codperiod', $filters['period']);
        } else {
            $query->where('per.is_active', 'Y');
        }

        if (!empty($filters['grade']))   $query->where('g.codgrade',  $filters['grade']);
        if (!empty($filters['section'])) $query->where('gs.section',  $filters['section']);
        if (!empty($filters['session'])) $query->where('a.codassistance_session', $filters['session']);

        return $query;
    }

    private function filtersFrom(Request $request): array
    {
        return [
            'period'  => $request->input('period'),
            'grade'   => $request->input('grade'),
            'section' => $request->input('section'),
            'session' => $request->input('session'),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // GET /guest/api/sessions
    // ════════════════════════════════════════════════════════════════════════
    public function sessions(Request $request)
    {
        $filters = $this->filtersFrom($request);

        $sessions = DB::table('system.assistance_session as s')
            ->join('system.schedules as sc', 'sc.codschedule', '=', 's.codschedule')
            ->whereNull('s.deleted_at')
            ->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('system.assistance as a')
                    ->join('system.enrollment as e',      'e.codenrollment',      '=', 'a.codenrollment')
                    ->join('system.period as per',        'per.codperiod',        '=', 'e.codperiod')
                    ->join('system.grade_schedule as gs', 'gs.codgrade_schedule', '=', 'e.codgrade_schedule')
                    ->join('main.grade as g',             'g.codgrade',           '=', 'gs.codgrade')
                    ->whereColumn('a.codassistance_session', 's.codassistance_session')
                    ->whereNull('a.deleted_at')
                    ->when(
                        !empty($filters['period']),
                        fn ($q) => $q->where('e.codperiod', $filters['period']),
                        fn ($q) => $q->where('per.is_active', 'Y')
                    )
                    ->when(!empty($filters['grade']),   fn ($q) => $q->where('g.codgrade',  $filters['grade']))
                    ->when(!empty($filters['section']), fn ($q) => $q->where('gs.section',  $filters['section']));
            })
            ->select(
                's.codassistance_session',
                DB::raw("TO_CHAR(s.date, 'DD/MM/YYYY') AS date"),
                's.date AS raw_date',
                'sc.turn'
            )
            ->orderByDesc('s.date')
            ->limit(120)
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // GET /guest/api/records
    // ════════════════════════════════════════════════════════════════════════
    public function records(Request $request)
    {
        $query = $this->applyFilters($this->baseQuery(), $this->filtersFrom($request))
            ->select(
                'p.identify_number                                          AS dni',
                DB::raw("CONCAT(p.firstname,' ',p.lastname_father,' ',COALESCE(p.lastname_mom,'')) AS fullname"),
                'g.name_short                                               AS grade',
                'gs.section',
                'sc.turn',
                'per.name                                                   AS period',
                DB::raw("TO_CHAR(s.date,'DD/MM/YYYY')                      AS date"),
                DB::raw("TO_CHAR(a.time_entry,'HH24:MI')                   AS time_entry"),
                'a.status',
                'a.observation'
            )
            ->orderByDesc('s.date')
            ->orderBy('g.name_short')
            ->orderBy('gs.section');

        if ($request->filled('status')) {
            $query->where('a.status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('p.identify_number', 'ILIKE', "%{$search}%")
                  ->orWhere('p.firstname',       'ILIKE', "%{$search}%")
                  ->orWhere('p.lastname_father', 'ILIKE', "%{$search}%")
                  ->orWhere('p.lastname_mom',    'ILIKE', "%{$search}%");
            });
        }

        return response()->json(['records' => $query->limit(2000)->get()]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // GET /guest/api/student
    //
    // Devuelve student + enrollment + assistances + stats.
    // Las asistencias incluyen `period_code` para que el filtro del panel
    // pueda filtrar por periodo en el cliente sin otra llamada AJAX.
    // ════════════════════════════════════════════════════════════════════════
    public function student(Request $request)
    {
        $dni = trim($request->input('dni', ''));

        if (strlen($dni) < 3) {
            return response()->json([
                'student'     => null,
                'enrollment'  => null,
                'assistances' => [],
                'stats'       => null,
            ]);
        }

        // ── Buscar alumno por DNI (búsqueda parcial) ─────────────────────
        $student = DB::table('system.student as st')
            ->join('main.person as p', 'p.codperson', '=', 'st.codperson')
            ->whereNull('st.deleted_at')
            ->where('p.identify_number', 'ILIKE', "%{$dni}%")
            ->select(
                'st.codstudent',
                'p.identify_number AS dni',
                DB::raw("TRIM(CONCAT(p.firstname,' ',p.lastname_father,' ',COALESCE(p.lastname_mom,''))) AS fullname"),
                'p.phone'
            )
            ->first();

        if (!$student) {
            return response()->json([
                'student'     => null,
                'enrollment'  => null,
                'assistances' => [],
                'stats'       => null,
            ]);
        }

        // ── Matrícula activa (o del periodo solicitado) ───────────────────
        $enrollmentQ = DB::table('system.enrollment as e')
            ->join('system.period as per',        'per.codperiod',        '=', 'e.codperiod')
            ->join('system.grade_schedule as gs', 'gs.codgrade_schedule', '=', 'e.codgrade_schedule')
            ->join('main.grade as g',             'g.codgrade',           '=', 'gs.codgrade')
            ->join('system.schedules as sc',      'sc.codschedule',       '=', 'gs.codschedule')
            ->where('e.codstudent', $student->codstudent)
            ->whereNull('e.deleted_at');

        if ($request->filled('period')) {
            $enrollmentQ->where('e.codperiod', $request->input('period'));
        } else {
            $enrollmentQ->where('per.is_active', 'Y');
        }

        $enrollment = $enrollmentQ
            ->select(
                'e.codenrollment',
                'e.codperiod',
                'per.name     AS period',
                'g.name_short AS grade',
                'gs.section',
                'sc.turn'
            )
            ->first();

        // ── Historial de asistencias (TODOS los periodos del alumno) ──────
        // Se incluye `e.codperiod` como `period_code` para filtrado cliente.
        $assistances = [];
        $stats       = null;

        if ($enrollment) {
            $rows = DB::table('system.assistance as a')
                ->join('system.assistance_session as s', 's.codassistance_session', '=', 'a.codassistance_session')
                ->join('system.enrollment as e',         'e.codenrollment',         '=', 'a.codenrollment')
                ->join('system.period as per',           'per.codperiod',           '=', 'e.codperiod')
                ->join('system.schedules as sc',         'sc.codschedule',          '=', 's.codschedule')
                ->where('e.codstudent', $student->codstudent)   // todos los periodos del alumno
                ->whereNull('a.deleted_at')
                ->select(
                    DB::raw("TO_CHAR(s.date,'DD/MM/YYYY')    AS date"),
                    's.date                                   AS raw_date',
                    'sc.turn',
                    'e.codperiod                              AS period_code',
                    'per.name                                 AS period_name',
                    DB::raw("TO_CHAR(a.time_entry,'HH24:MI') AS time_entry"),
                    'a.status',
                    'a.observation'
                )
                ->orderByDesc('s.date')
                ->get();

            $assistances = $rows->values()->toArray();

            // Stats calculadas sobre el periodo de matrícula activa
            $periodRows  = $rows->where('period_code', $enrollment->codperiod);
            $total       = $periodRows->count();
            $present     = $periodRows->where('status', 'present')->count();
            $absent      = $periodRows->where('status', 'absent')->count();
            $late        = $periodRows->where('status', 'late')->count();

            $stats = [
                'total'   => $total,
                'present' => $present,
                'absent'  => $absent,
                'late'    => $late,
                'rate'    => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        } else {
            // Sin matrícula activa: buscar en cualquier periodo
            $rows = DB::table('system.assistance as a')
                ->join('system.assistance_session as s', 's.codassistance_session', '=', 'a.codassistance_session')
                ->join('system.enrollment as e',         'e.codenrollment',         '=', 'a.codenrollment')
                ->join('system.period as per',           'per.codperiod',           '=', 'e.codperiod')
                ->join('system.schedules as sc',         'sc.codschedule',          '=', 's.codschedule')
                ->where('e.codstudent', $student->codstudent)
                ->whereNull('a.deleted_at')
                ->select(
                    DB::raw("TO_CHAR(s.date,'DD/MM/YYYY')    AS date"),
                    's.date                                   AS raw_date',
                    'sc.turn',
                    'e.codperiod                              AS period_code',
                    'per.name                                 AS period_name',
                    DB::raw("TO_CHAR(a.time_entry,'HH24:MI') AS time_entry"),
                    'a.status',
                    'a.observation'
                )
                ->orderByDesc('s.date')
                ->get();

            $assistances = $rows->values()->toArray();
        }

        return response()->json([
            'student'     => $student,
            'enrollment'  => $enrollment,
            'assistances' => $assistances,
            'stats'       => $stats,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // GET /guest/api/sections
    // ════════════════════════════════════════════════════════════════════════
    public function sections(Request $request)
    {
        $query = DB::table('system.grade_schedule as gs')
            ->join('system.enrollment as e', 'e.codgrade_schedule', '=', 'gs.codgrade_schedule')
            ->join('system.period as per',   'per.codperiod',       '=', 'e.codperiod')
            ->join('main.grade as g',        'g.codgrade',          '=', 'gs.codgrade')
            ->whereNull('gs.deleted_at')
            ->whereNull('e.deleted_at');

        if ($request->filled('period')) {
            $query->where('e.codperiod', $request->input('period'));
        } else {
            $query->where('per.is_active', 'Y');
        }

        if ($request->filled('grade')) {
            $query->where('gs.codgrade', $request->input('grade'));
        }

        $sections = $query->pluck('gs.section')->unique()->sort()->values();

        return response()->json(['sections' => $sections]);
    }
}