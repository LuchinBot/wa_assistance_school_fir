<?php
// app/Http/Controllers/GradeScheduleController.php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\System\GradeSchedule;
use App\Models\System\Schedules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GradeScheduleController extends Controller
{
    public $extend = null;
    protected $perPage = 10;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:crear')->only('store');
        $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title'       => 'Horarios por Grado',
            'title_form'  => 'Asignación de Horario',
            'view' => 'list',
            'controller'  => 'grade_schedule',
            'totalRecord' => GradeSchedule::count(),
        ];
    }

    public function index()
    {
        $data = GradeSchedule::with('grade.level', 'schedule')
            ->orderBy('codgrade_schedule', 'DESC')
            ->limit($this->perPage)
            ->get();

        $grades    = Grade::with('level')->orderBy('codgrade')->get();
        $schedules = Schedules::orderBy('turn')->get();

        return view('grade_schedule.list', [
            'extend'    => $this->extend,
            'data'      => $data,
            'grades'    => $grades,
            'schedules' => $schedules,
        ]);
    }

    public function form(Request $request, $id = null)
    {
        $grade_schedule = $id ? GradeSchedule::find($id) : null;
        $grades    = Grade::with('level')->orderBy('name_large')->get();
        $schedules = Schedules::orderBy('turn')->get();

        $this->extend['view'] = 'form';

        return view('grade_schedule.form', [
            'extend'    => $this->extend,
            'grade_schedule'      => $grade_schedule,
            'grades'    => $grades,
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'codgrade'    => ['required', 'integer', 'exists:grade,codgrade'],
            'codschedule' => ['required', 'integer', 'exists:schedules,codschedule'],
            'section'     => ['required', 'string', 'max:255'], // ahora puede ser A,B,C
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 🔹 Convertir a array (A,B,C → ['A','B','C'])
            $sections = array_map('trim', explode(',', $request->section));

            $created = [];
            $skipped = [];

            foreach ($sections as $section) {

                // Evitar vacíos tipo "A,,B"
                if (!$section) continue;

                $exists = GradeSchedule::where('codgrade', $request->codgrade)
                    ->where('codschedule', $request->codschedule)
                    ->where('section', $section)
                    ->exists();

                if ($exists) {
                    $skipped[] = $section;
                    continue;
                }

                $record = GradeSchedule::create([
                    'codgrade'    => $request->codgrade,
                    'codschedule' => $request->codschedule,
                    'section'     => $section,
                ]);

                $created[] = $record;
            }

            $ids = collect($created)->pluck('codgrade_schedule');

            $data = GradeSchedule::with('grade.level', 'schedule')
                ->whereIn('codgrade_schedule', $ids)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Proceso completado',
                'created' => count($created),
                'skipped' => $skipped,
                'data'    => $data,
                'totalRecords' => GradeSchedule::count(),
                'redirect' => route('grade_schedule.list'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function records($from, $to, $keyword = 'null')
    {
        $query = GradeSchedule::with('grade.level', 'schedule')
            ->orderBy('codgrade_schedule', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas(
                    'grade',
                    fn($g) =>
                    $g->where('name_large', 'ILIKE', "%{$keyword}%")
                        ->orWhere('name_short', 'ILIKE', "%{$keyword}%")
                )->orWhereHas(
                    'schedule',
                    fn($s) =>
                    $s->where('turn', 'ILIKE', "%{$keyword}%")
                );
            });
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

    public function destroy($id)
    {
        try {
            GradeSchedule::findOrFail($id)->delete();

            return response()->json([
                'success'      => true,
                'message'      => 'Asignación eliminada correctamente',
                'totalRecords' => GradeSchedule::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dado un horario, retorna los grados asignados
     */
    public function gradesBySchedule($codschedule)
    {
        // Quita el eager load de students, no lo necesitas aquí
        $grades = GradeSchedule::with('grade.level') // ← sin students
            ->where('codschedule', $codschedule)
            ->whereNull('deleted_at')
            ->get()
            ->pluck('grade')
            ->unique('codgrade')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $grades,
        ]);
    }
    // Dado un grado + horario, retorna las secciones disponibles
    public function sections(Request $request)
    {
        $codgrade    = $request->query('grade');
        $codschedule = $request->query('schedule');

        $sections = GradeSchedule::where('codgrade', $codgrade)
            ->where('codschedule', $codschedule)
            ->get(['codgrade_schedule', 'section']);

        return response()->json([
            'success' => true,
            'data'    => $sections,
        ]);
    }
}
