<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\TypeDocumentIdentify;
use App\Models\Gender;
use App\Models\CivilStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PersonController extends Controller
{
    public $extend = null;
    public $keyword;
    protected $perPage = 15;

    protected $is_super = null;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:editar')->only('form');
        $this->middleware('module.permission:crear')->only(['store']);
        $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title' => 'Personas',
            'title_form' => 'Persona',
            'view' => 'list',
            'controller' => 'person',
            'totalRecord' => Person::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();

        $query = Person::query();

        if ($user->is_super !== 'Y') {
            $query->whereDoesntHave('users', function ($q) {
                $q->where('is_super', 'Y');
            });
        }

        $data = $query
            ->orderByDesc('codperson')
            ->paginate($this->perPage);

        return view('person.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }


    public function form(Request $request, $id = null)
    {
        $person = $id ? Person::find($id) : null;
        $this->extend['view'] = 'form';

        return view('person.form', [
            'extend' => $this->extend,
            'person' => $person,
            'documentTypes' => TypeDocumentIdentify::all(),
            'genders' => Gender::all(),
            'civilStatuses' => CivilStatus::all(),
            'redirect' => $request->get('redirect')
        ]);
    }

    /**
     * Guardar o actualizar registro
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'codtd_identify' => 'required|integer',
            'identify_number' => [
                'required',
                'string',
                'max:12',
                Rule::unique('person', 'identify_number')->ignore($id, 'codperson')
            ],
            'firstname' => 'required|string|max:100',
            'lastname_father' => 'required|string|max:200',
            'lastname_mom' => 'required|string|max:200',
            'email' => [
                'nullable',
                'email',
                'max:90',
                Rule::unique('person', 'email')->ignore($id, 'codperson')
            ],
            'codubigeo' => 'nullable|integer|exists:ubigeo,codubigeo',
            'codgender' => 'nullable|integer',
            'codcivil_status' => 'nullable|integer',
            'department' => 'nullable|string',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'birthday' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $person = $id ? Person::findOrFail($id) : new Person();

            // 1. Datos básicos
            $data = $request->except(['photo', 'firma', '_token']);
            $cleanIdentify = preg_replace('/[^A-Za-z0-9]/', '', $request->identify_number);

            // 2. Manejo de archivos hacia servidor central
            $files = ['photo', 'firma'];

            foreach ($files as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = $field . '_' . $cleanIdentify . '_' . time() . '.' .
                        $file->getClientOriginalExtension();

                    $response = Http::attach(
                        'file',
                        fopen($file->getRealPath(), 'r'),
                        $filename
                    )->post(
                        config('app.files_url') . '/api/upload',
                        [
                            'folder' => 'persons'
                        ]
                    );

                    if (!$response->successful()) {
                        throw new \Exception("Error subiendo archivo {$field}");
                    }

                    $fileData = $response->json();
                    if ($id && $person->$field) {
                        Http::post(
                            config('app.files_url') . '/api/delete',
                            [
                                'path' => $person->$field
                            ]
                        );
                    }

                    $data[$field] = $fileData['path'];
                }
            }

            $person->fill($data);
            $person->save();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Persona actualizada correctamente' : 'Persona creada correctamente',
                'data' => $person,
                'totalRecords' => Person::count(),
                'redirect' => $request->input('redirect') ?? route('person.list')
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
        $query = Person::orderBy('codperson', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%")
                    ->orWhere('email', 'ILIKE', "%{$keyword}%");
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

        $query = Person::orderBy('codperson', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('firstname', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                    ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$keyword}%")
                    ->orWhere('email', 'ILIKE', "%{$keyword}%");
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
            $person = Person::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $person
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
            $person = Person::findOrFail($id);

            // Validar relaciones antes de borrar
            if ($person->users()->exists()) { // 'users' es la relación con la tabla user
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar esta persona porque tiene usuarios asociados.'
                ], 400); // 400 Bad Request
            }

            // Borrar foto si existe
            if ($person->photo) {
                Http::post(config('app.files_url') . '/api/delete', [
                    'path' => $person->photo
                ]);
            }

            $person->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Person::count()
            ]);
        } catch (\Exception $e) {
            // Log del error para depuración
            Log::error('Error eliminando persona: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el registro.'
            ], 500);
        }
    }
}
