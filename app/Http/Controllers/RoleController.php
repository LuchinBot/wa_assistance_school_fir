<?php

namespace App\Http\Controllers;

use App\Models\Security\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
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
            'title' => 'Roles',
            'title_form' => 'Rol',
            'view' => 'list',
            'controller' => 'role',
            'totalRecord' => Profile::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Profile::orderBy('codprofile', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('role.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null)
    {
        $profile = $id ? Profile::find($id) : null;
        $this->extend['view'] = 'form';

        return view('role.form', [
            'extend' => $this->extend,
            'profile' => $profile,
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'name_large' => [
                'required',
                'string',
                'max:250',
                Rule::unique('profile', 'name_large')->ignore($id, 'codprofile')
            ],
            'name_short' => [
                'required',
                'string',
                'max:250',
                Rule::unique('profile', 'name_short')->ignore($id, 'codprofile')
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
                $profile = Profile::findOrFail($id);
                $profile->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $profile = Profile::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $profile,
                'totalRecords' => Profile::count()
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
        $query = Profile::orderBy('codprofile', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name_large', 'ILIKE', "%{$keyword}%")
                    ->orWhere('name_short', 'ILIKE', "%{$keyword}%");
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

        $query = Profile::orderBy('codprofile', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name_large', 'ILIKE', "%{$keyword}%")
                    ->orWhere('name_short', 'ILIKE', "%{$keyword}%");
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
            $profile = Profile::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $profile
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
            $profile = Profile::findOrFail($id);

            // Eliminar foto si existe
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }

            $profile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Profile::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
