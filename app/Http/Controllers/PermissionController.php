<?php

namespace App\Http\Controllers;

use App\Models\Security\Module;
use App\Models\Security\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
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
            'title' => 'Permisos',
            'title_form' => 'Permiso',
            'view' => 'list',
            'controller' => 'permission',
            'totalRecord' => Permission::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Permission::with('module')->orderBy('codpermission', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('permission.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $permissions = $id ? Permission::find($id) : null;
        $modules = Module::get();
        $this->extend['view'] = 'form';

        return view('permission.form', [
            'extend' => $this->extend,
            'permissions' => $permissions,
            'modules' => $modules
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'codmodule' => 'required|exists:modules,codmodule',
            //'name' => 'required_without:create_default_permissions|string|max:250',
            'description' => 'nullable|string|max:250',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            // ============================================
            // 🔹 CREAR PERMISOS POR DEFECTO
            // ============================================
            if ($request->has('create_default_permissions') && !$id) {

                $actions = ['Listar', 'Crear', 'Editar', 'Eliminar'];
                $created = [];

                foreach ($actions as $action) {

                    $name = "{$action} {$request->codmodule}";

                    // Evitar duplicados
                    $exists = Permission::where('codmodule', $request->codmodule)
                        ->whereRaw('LOWER(name) LIKE ?', [strtolower($action) . '%'])
                        ->exists();

                    if (!$exists) {
                        $created[] = Permission::create([
                            'codmodule' => $request->codmodule,
                            'name' => "{$action} " . Module::find($request->codmodule)->name_large,
                            'description' => "Permite {$action} registros del módulo"
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Permisos por defecto creados correctamente',
                    'data' => $created,
                    'totalRecords' => Permission::count()
                ]);
            }

            // ============================================
            // 🔹 CREACIÓN / EDICIÓN NORMAL
            // ============================================
            $data = $request->except(['_token', 'create_default_permissions']);

            if ($id) {
                $permission = Permission::findOrFail($id);
                $permission->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $permission = Permission::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $permission,
                'totalRecords' => Permission::count()
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
        $query = Permission::with('module','module.parent','module.children')->orderBy('codpermission', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'ILIKE', "%{$keyword}%")
                    ->orWhere('description', 'ILIKE', "%{$keyword}%");
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

        $query = Permission::with('module','module.parent','module.children')->orderBy('codpermission', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'ILIKE', "%{$keyword}%")
                    ->orWhere('description', 'ILIKE', "%{$keyword}%");
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
            $permissions = Permission::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $permissions
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
            $permissions = Permission::findOrFail($id);

            $permissions->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Permission::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
