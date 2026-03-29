<?php

namespace App\Http\Controllers;

use App\Models\Security\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
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
            'title' => 'Módulos',
            'title_form' => 'Módulo',
            'view' => 'list',
            'controller' => 'module',
            'totalRecord' => Module::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Module::with('parent','children')->orderBy('codmodule', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('module.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $modules = $id ? Module::find($id) : null;

        $modules_father = Module::whereNull('codmodule_parent')->get();
        $this->extend['view'] = 'form';

        return view('module.form', [
            'extend' => $this->extend,
            'modules' => $modules,
            'modules_father' => $modules_father
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $parentId = $request->input('codmodule_parent'); // o la clave que uses

        $rules = [
            'name_large' => [
                'required',
                'string',
                'max:250',
                Rule::unique('modules', 'name_large')
                    ->where(function ($q) use ($parentId) {
                        $q->where('codmodule_parent', $parentId);
                    })
                    ->ignore($id, 'codmodule'),
            ],
            'name_short' => [
                'nullable',
                'string',
                'max:250',
                Rule::unique('modules', 'name_short')
                    ->where(function ($q) use ($parentId) {
                        $q->where('codmodule_parent', $parentId);
                    })
                    ->ignore($id, 'codmodule'),
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
                $modules = Module::findOrFail($id);
                $modules->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $modules = Module::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $modules,
                'totalRecords' => Module::count(),
                'redirect'     => $request->redirect ?? route('module.list')
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
        $query = Module::with('parent','children')->orderBy('codmodule', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name_large', 'ILIKE', "%{$keyword}%")
                    ->orWhere('name_short', 'ILIKE', "%{$keyword}%")
                    ->orWhere('icon', 'ILIKE', "%{$keyword}%");
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

        $query = Module::with('parent','childred')->orderBy('codmodule', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name_large', 'ILIKE', "%{$keyword}%")
                    ->orWhere('name_short', 'ILIKE', "%{$keyword}%")
                    ->orWhere('icon', 'ILIKE', "%{$keyword}%");
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
            $modules = Module::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $modules
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
            $modules = Module::findOrFail($id);

            $modules->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Module::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
