<?php

namespace App\Http\Controllers;

use App\Models\Param;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ParamController extends Controller
{
    public $extend = null;
    public $keyword;
    protected $perPage = 10;

    public function __construct()
    {
        // $this->middleware('module.permission:listar')->only('index');
        // $this->middleware('module.permission:editar')->only('form');
        // $this->middleware('module.permission:crear')->only(['store']);
        // $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title' => 'Parámetros',
            'title_form'=>'Parametro',
            'controller' => 'param',
            'totalRecord' => Param::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Param::orderBy('codparam', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('param.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $param = $id ? Param::find($id) : null;

        return view('param.form', [
            'extend' => $this->extend,
            'param' => $param,
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'param' => [
                'required',
                'string',
                'max:250',
            ],
            'value' => [
                'required',
                'string',
                'max:250',
            ],
            'description' => [
                'nullable',
                'string',
                'max:250',
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
            $data = $request->except(['photo', '_token']);


            if ($id) {
                $param = Param::findOrFail($id);
                $param->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $param = Param::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $param,
                'totalRecords' => Param::count()
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
        $query = Param::orderBy('codparam', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('param', 'ILIKE', "%{$keyword}%")
                    ->orWhere('value', 'ILIKE', "%{$keyword}%");
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

        $query = Param::orderBy('codparam', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('param', 'ILIKE', "%{$keyword}%")
                    ->orWhere('value', 'ILIKE', "%{$keyword}%");
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
            $param = Param::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $param
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
            $param = Param::findOrFail($id);

            // Eliminar foto si existe
            if ($param->photo) {
                Storage::disk('public')->delete($param->photo);
            }

            $param->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Param::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
