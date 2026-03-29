<?php

namespace App\Http\Controllers;

use App\Models\System\CourseOpening;
use App\Models\System\Course;
use App\Models\System\CourseGroup;
use App\Models\System\CourseModality;
use App\Models\System\Classroom;
use App\Models\System\Teacher;
use App\Models\Filial;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CourseOpeningController extends Controller
{
    public $extend = null;
    public $keyword;
    protected $perPage = 15;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:editar')->only('form');
        $this->middleware('module.permission:crear')->only(['store']);
        $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title' => 'Apertura de Cursos',
            'title_form' => 'Curso Aperturado',
            'controller' => 'course_opening',
            'totalRecord' => CourseOpening::count(),
        ];

        $this->keyword = null;
    }

    /**
     * Listado
     */
    public function index()
    {
        $data = CourseOpening::with([
            'course',
            'group',
            'modality',
            'classroom',
            'filial',
            'teacher'
        ])
            ->orderBy('course_opening', 'DESC')
            ->limit($this->perPage)
            ->get();

        return view('course_opening.list', [
            'extend' => $this->extend,
            'data' => $data
        ]);
    }

    /**
     * Formulario crear / editar
     */
    public function form(Request $request, $id = null)
    {
        $courseOpening = $id
            ? CourseOpening::find($id)
            : null;

        return view('course_opening.form', [
            'extend' => $this->extend,
            'courseOpening' => $courseOpening,
            'courses' => Course::all(),
            'groups' => CourseGroup::all(),
            'modalities' => CourseModality::all(),
            'classrooms' => Classroom::all(),
            'filials' => Filial::all(),
            'teachers' => Teacher::with('person')->get(),
            'redirect' => $request->get('redirect')
        ]);
    }

    /**
     * Guardar / actualizar
     */
    public function store(Request $request, $id = null)
    {
        $rules = [
            'codcourse' => 'required|integer',
            'codcourse_group' => 'required|integer',
            'codcourse_modality' => 'required|integer',
            'codclassroom' => 'required|integer',
            'codfilial' => 'required|integer',
            'codteacher' => 'required|integer',
            'web_inscription' => 'nullable|boolean',
            'web_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $courseOpening = $id
                ? CourseOpening::findOrFail($id)
                : new CourseOpening();

            $data = $request->except(['web_image', '_token']);

            /*
            |--------------------------------------------------------------------------
            | Imagen Web
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('web_image')) {

                $file = $request->file('web_image');

                $filename = 'course_opening_' . time() . '.' .
                    $file->getClientOriginalExtension();

                $path = $file->storeAs('course_openings', $filename, 'public');

                if ($path) {

                    if ($id && $courseOpening->web_image) {
                        Storage::disk('public')->delete($courseOpening->web_image);
                    }

                    $data['web_image'] = $path;
                }
            }

            $courseOpening->fill($data);
            $courseOpening->save();

            return response()->json([
                'success' => true,
                'message' => $id
                    ? 'Curso aperturado actualizado correctamente'
                    : 'Curso aperturado creado correctamente',
                'data' => $courseOpening,
                'totalRecords' => CourseOpening::count(),
                'redirect' => $request->input('redirect')
                    ?? route('course_opening.list')
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Records paginados
     */
    public function records($from, $to, $keyword = 'null')
    {
        $query = CourseOpening::with('course')
            ->orderBy('course_opening', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->whereHas('course', function ($q) use ($keyword) {
                $q->where('name_large', 'ILIKE', "%{$keyword}%");
            });
        }

        $total = $query->count();

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

    /**
     * Buscar
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query = CourseOpening::with('course')
            ->orderBy('course_opening', 'DESC');

        if (!empty($keyword)) {
            $query->whereHas('course', function ($q) use ($keyword) {
                $q->where('name_large', 'ILIKE', "%{$keyword}%");
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
     * Mostrar uno
     */
    public function show($id)
    {
        try {

            $courseOpening = CourseOpening::with([
                'course',
                'group',
                'modality',
                'classroom',
                'filial',
                'teacher'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $courseOpening
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }
    }

    /**
     * Eliminar
     */
    public function destroy($id)
    {
        $courseOpening = CourseOpening::findOrFail($id);

        if ($courseOpening->web_image) {
            Storage::disk('public')->delete($courseOpening->web_image);
        }

        $courseOpening->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
            'totalRecords' => CourseOpening::count()
        ]);
    }
}
