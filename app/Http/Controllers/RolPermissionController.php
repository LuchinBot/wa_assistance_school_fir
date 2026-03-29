<?php

namespace App\Http\Controllers;

use App\Models\Security\Profile;
use App\Models\Security\ProfilePermission;
use App\Models\Security\Module;
use App\Models\Security\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RolPermissionController extends Controller
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
            'title' => 'Permisos de Rol',
            'title_form' => 'Permiso de Rol',
            'view' => 'list',
            'controller' => 'rolpermission',
            'totalRecord' => ProfilePermission::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = ProfilePermission::with('profile', 'permission')->orderBy('codprofile_permission', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('rolpermission.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $rolpermissions = $id ? ProfilePermission::find($id) : null;
        $profiles = Profile::get();
        $permissions = Permission::get();
        $this->extend['view'] = 'form';

        return view('rolpermission.form', [
            'extend' => $this->extend,
            'rolpermissions' => $rolpermissions,
            'profiles' => $profiles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'codprofile' => 'required|exists:profile,codprofile',
            'codpermission' => 'required|array|min:1',
            'codpermission.*' => 'exists:permissions,codpermission',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $keywords = ['listar', 'crear', 'editar', 'eliminar'];

        foreach ($request->codpermission as $codpermission) {

            // 🔹 VALIDACIÓN 1: NO DUPLICAR EL MISMO PERMISO
            $alreadyAssigned = ProfilePermission::where('codprofile', $request->codprofile)
                ->where('codpermission', $codpermission)
                ->exists();

            if ($alreadyAssigned) {
                $permissionName = Permission::find($codpermission)->name ?? '';
                return response()->json([
                    'success' => false,
                    'message' => "El perfil ya tiene asignado el permiso: {$permissionName}"
                ], 422);
            }

            $permission = Permission::with('module')->find($codpermission);
            if (!$permission) {
                continue;
            }

            // 🔹 VALIDACIÓN 2: SOLO UN listar/crear/editar/eliminar POR MÓDULO
            $keywordFound = null;

            foreach ($keywords as $key) {
                if (str_contains(strtolower($permission->name), $key)) {
                    $keywordFound = $key;
                    break;
                }
            }

            if ($keywordFound) {

                $exists = ProfilePermission::where('codprofile', $request->codprofile)
                    ->whereHas('permission', function ($q) use ($keywordFound, $permission) {
                        $q->where('codmodule', $permission->codmodule)
                            ->whereRaw('LOWER(name) LIKE ?', ["%{$keywordFound}%"]);
                    })
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => "El perfil ya tiene un permiso '{$keywordFound}' en el módulo {$permission->module->name}"
                    ], 422);
                }
            }
        }

        try {

            foreach ($request->codpermission as $codpermission) {
                ProfilePermission::create([
                    'codprofile' => $request->codprofile,
                    'codpermission' => $codpermission
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Permisos asignados correctamente',
                'totalRecords' => ProfilePermission::count()
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
        $query = ProfilePermission::with(['profile', 'permission'])
            ->orderBy('codprofile_permission', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {

                // 🔹 Buscar por nombre del permiso
                $q->whereHas('permission', function ($p) use ($keyword) {
                    $p->where('name', 'ILIKE', "%{$keyword}%")
                        ->orWhere('description', 'ILIKE', "%{$keyword}%");
                })

                    // 🔹 (opcional) buscar por nombre del perfil
                    ->orWhereHas('profile', function ($r) use ($keyword) {
                        $r->where('name_large', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = $query->count();

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


    /**
     * Buscar registros
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query = ProfilePermission::with(['profile', 'permission'])
            ->orderBy('codprofile_permission', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {

                $q->whereHas('permission', function ($p) use ($keyword) {
                    $p->where('name', 'ILIKE', "%{$keyword}%")
                        ->orWhere('description', 'ILIKE', "%{$keyword}%");
                })

                    ->orWhereHas('profile', function ($r) use ($keyword) {
                        $r->where('name_large', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = $query->count();
        $data = $query->limit($this->perPage)->get();

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
            $rolpermissions = ProfilePermission::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $rolpermissions
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
            $rolpermissions = ProfilePermission::findOrFail($id);

            $rolpermissions->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => ProfilePermission::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
