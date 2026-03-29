<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Person;
use App\Models\Profession;
use App\Models\System\Student;
use App\Models\System\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\System\Assignee;


class AssigneeController extends Controller
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
            'title' => 'Apoderados',
            'title_form' => 'Apoderado',
            'view' => 'list',
            'controller' => 'assignee',
            'totalRecord' => Assignee::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();

        $data = Assignee::with('person')
            ->orderBy('codassignee', 'DESC')
            ->limit($this->perPage)
            ->get();
        return view('assignee.list', [
            'extend' => $this->extend,
            'data' => $data,

        ]);
    }


    /**
     * Mostrar formulario de creación/edición
     */
    public function form(Request $request, $id = null)
    {
        $user = Auth::user();

        $assignees = $id
            ? Assignee::with('person')->findOrFail($id)
            : null;

        $persons = Person::orderByDesc('codperson')->get();

        $this->extend['view'] = 'form';

        return view('assignee.form', [
            'extend' => $this->extend,
            'assignees' => $assignees,
            'persons' => $persons,
            'redirect' => $request->get('redirect') ?? route('assignee.list'),
        ]);
    }


    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'codperson' => [
                'required',
                Rule::exists('person', 'codperson')
            ],
            'relationship' => [
                'required',
                'string',
                'max:100'
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
            $data = $request->all();

            if ($id) {
                $student = Assignee::findOrFail($id);
                $student->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $student = Assignee::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $student,
                'totalRecords' => Assignee::count(),
                'redirect' => $request->redirect ?? route('assignee.list')

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function records($from, $to, $keyword = null)
    {
        $user = Auth::user();

        $query = Assignee::with('person')
            ->orderBy('codassignee', 'DESC');

        if (!empty($keyword) && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('codassignee', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = (clone $query)->count(); // evitar que afecte el query original
        $data = $query->skip($from)
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
    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $user = Auth::user();

        $query = Assignee::with('person')
            ->orderBy('codassignee', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('codassignee', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = (clone $query)->count();

        $data = $query->limit($this->perPage)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'keyword' => $keyword
        ]);
    }


    public function destroy($id)
    {
        try {
            $permissions = Assignee::findOrFail($id);

            $permissions->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Assignee::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
