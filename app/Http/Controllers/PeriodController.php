<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Person;
use App\Models\System\Periods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodController extends Controller
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
            'title' => 'Periodos',
            'title_form' => 'periodo',
            'controller' => 'period',
            'totalRecord' => Periods::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Periods::orderBy('codperiod', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('period.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $period = Periods::find($id);

        return view('period.form', [
            'extend' => $this->extend,
            'period' => $period
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'name_year' => [
                'required',
                'string',
                'max:4',
                $id ? 'unique:periods,name_year,' . $id . ',codperiod' : 'unique:periods,name_year',
            ],
            'actually' => 'nullable|in:Y,N,1,0',
            'open_registration' => 'nullable|in:Y,N,1,0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar que solo exista un actually = Y
        if ($request->actually === 'Y' || $request->actually === '1') {
            $exists = Periods::where('actually', 'Y')
                ->when($id, fn($q) => $q->where('codperiod', '!=', $id))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'errors' => ['actually' => ['Ya existe un periodo marcado como actual.']]
                ], 422);
            }
        }

        // Validar que solo exista un open_registration = Y
        if ($request->open_registration === 'Y' || $request->open_registration === '1') {
            $exists = Periods::where('open_registration', 'Y')
                ->when($id, fn($q) => $q->where('codperiod', '!=', $id))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'errors' => ['open_registration' => ['Ya existe un periodo con inscripciones abiertas.']]
                ], 422);
            }
        }

        try {
            $data = $request->except(['photo', '_token']);

            // Normalizar a 'Y' o 'N' antes de guardar
            $data['actually'] = in_array($data['actually'] ?? null, ['Y', '1']) ? 'Y' : 'N';
            $data['open_registration'] = in_array($data['open_registration'] ?? null, ['Y', '1']) ? 'Y' : 'N';

            if ($id) {
                $period = Periods::findOrFail($id);
                $period->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $period = Periods::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $period,
                'totalRecords' => Periods::count()
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
        $query = Periods::orderBy('codperiod', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name_year', 'ILIKE', "%{$keyword}%");
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

        $query = Periods::orderBy('codperiod', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name_year', 'ILIKE', "%{$keyword}%");
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
            $period = Periods::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $period
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
            $period = Periods::findOrFail($id);

            $period->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Periods::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
