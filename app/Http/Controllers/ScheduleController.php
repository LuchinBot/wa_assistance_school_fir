<?php

namespace App\Http\Controllers;

use App\Models\Security\Module;
use App\Models\System\Schedules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
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
            'title' => 'Horarios',
            'title_form' => 'Horario',
            'view' => 'list',
            'controller' => 'schedule',
            'totalRecord' => Schedules::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Schedules::orderBy('codschedule', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('schedule.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $schedules = $id ? Schedules::find($id) : null;

        $this->extend['view'] = 'form';
        return view('schedule.form', [
            'extend' => $this->extend,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $request->merge([
            'time_start' => strlen($request->time_start) === 5
                ? $request->time_start . ':00'
                : $request->time_start,

            'time_end' => strlen($request->time_end) === 5
                ? $request->time_end . ':00'
                : $request->time_end,
        ]);

        $rules = [
            'turn' => 'required|string|max:250',

            'time_start' => [
                'required',
                'date_format:H:i:s',
            ],

            'time_end' => [
                'required',
                'date_format:H:i:s',
            ],
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'turn',
                'time_start',
                'time_end'
            ]);

            if ($id) {
                $schedules = Schedules::findOrFail($id);
                $schedules->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $schedules = Schedules::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $schedules,
                'totalRecords' => Schedules::count(),
                'redirect'     => $request->redirect ?? route('schedule.list')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener registros paginados
     */
    public function records($from, $to, $keyword = 'null')
    {
        $query = Schedules::orderBy('codschedule', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('turn', 'ILIKE', "%{$keyword}%")
                    ->orWhere('time_start', 'ILIKE', "%{$keyword}%")
                    ->orWhere('time_end', 'ILIKE', "%{$keyword}%");
            });
        }

        $total = $query->count();
        $data = $query->skip($from)->take($to - $from)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'from' => $from,
            'to' => $to
        ]);
    }

    /**
     * Buscar registros
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query = Schedules::orderBy('codschedule', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('turn', 'ILIKE', "%{$keyword}%")
                    ->orWhere('time_start', 'ILIKE', "%{$keyword}%")
                    ->orWhere('time_end', 'ILIKE', "%{$keyword}%");
            });
        }

        $data = $query->limit($this->perPage)->get();
        $total = $query->count();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'keyword' => $keyword
        ]);
    }

    /**
     * Obtener un registro específico
     */
    public function show($id)
    {
        try {
            $schedules = Schedules::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }
    }

    /**
     * Eliminar registro
     */
    public function destroy($id)
    {
        try {
            $schedules = Schedules::findOrFail($id);

            $schedules->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Schedules::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
