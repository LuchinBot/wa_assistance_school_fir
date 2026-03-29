<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use App\Models\Security\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfessionController extends Controller
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
            'title' => 'Profesiones',
            'title_form' => 'Profesión',
            'controller' => 'profession',
            'totalRecord' => Profession::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();
        $data = Profession::orderBy('codprofession', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('profession.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Mostrar formulario de creación/edición
     */
    public function form(Request $request, $id = null)
    {
        $profession = $id ? Profession::find($id) : null;

        return view('profession.form', [
            'extend' => $this->extend,
            'profession' => $profession,
            'redirect' => $request->get('redirect')
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
                Rule::unique('profession', 'name_large')
                    ->ignore($id, 'codprofession')
                    ->whereNull('deleted_at'),
            ],
            'name_short' => [
                'nullable',
                'string',
                'max:250',
                Rule::unique('profession', 'name_short')
                    ->ignore($id, 'codprofession')
                    ->whereNull('deleted_at'),
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
                $professions = Profession::findOrFail($id);
                $professions->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $professions = Profession::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $professions,
                'totalRecords' => Profession::count(),
                'redirect' => $request->input('redirect') ?? route('profession.list')

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener registros paginados
     */
    public function records($from, $to, $keyword = 'null')
    {
        $query = Profession::orderBy('codprofession', 'DESC');

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

        $query = Profession::orderBy('codprofession', 'DESC');

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
            $professions = Profession::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $professions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profession no encontrada'
            ], 404);
        }
    }

    /**
     * Eliminar registro
     */
    public function destroy($id)
    {
        try {
            $professions = Profession::findOrFail($id);

            $professions->delete();

            return response()->json([
                'success' => true,
                'message' => 'Profession eliminada exitosamente',
                'totalRecords' => Profession::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
