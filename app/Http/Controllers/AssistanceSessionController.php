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
use App\Models\System\Assistance;
use App\Models\System\AssistanceSession;
use Carbon\Carbon;

class AssistanceSessionController extends Controller
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
            'title' => 'Asistencias',
            'title_form' => 'Asistencia',
            'controller' => 'assistance',
            'totalRecord' => Assistance::count(),
        ];
        $this->keyword = null;
    }

    /**
     * Mostrar lista de registros
     */
    public function index()
    {
        $user = Auth::user();

        $data = Assistance::with('student.person', 'assistance_session.schedule')
            ->orderBy('codassistance', 'DESC')
            ->limit($this->perPage)
            ->get();
        return view('assistance.list', [
            'extend' => $this->extend,
            'data' => $data,

        ]);
    }


    /**
     * Mostrar formulario de creación/edición
     */
    public function form($id = null, Request $request)
    {
        $user = Auth::user();

        $assistance = $id
            ? Assistance::with('student.person')->findOrFail($id)
            : null;

        // Filtrar persons por filial si aplica
        $persons = Person::orderByDesc('codperson')->get();

        return view('assistance.form', [
            'extend' => $this->extend,
            'assistance' => $assistance,
            'persons' => $persons,
            'redirect' => $request->get('redirect') ?? route('assistance.list'),
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
                $student = Assistance::findOrFail($id);
                $student->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $student = Assistance::create($data);
                $message = 'Registro creado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $student,
                'totalRecords' => Assistance::count(),
                'redirect' => $request->redirect ?? route('assistance.list')

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

        $query = Assistance::with('student.person', 'assistance_session.schedule')
            ->orderBy('codassistance', 'DESC');

        if (!empty($keyword) && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('codassistance', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('student.person', function ($p) use ($keyword) {
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

        $query = Assistance::with('student.person', 'assistance_session.schedule')
            ->orderBy('codassistance', 'DESC');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('codassistance', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('student.person', function ($p) use ($keyword) {
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
            $permissions = Assistance::findOrFail($id);

            $permissions->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'totalRecords' => Assistance::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateAttendanceExternal($dni, $late = null)
    {
        $dniStudent = $dni;
        $late = $late === 'late' ? true : false;

        // 1. Validar código
        if (!$dniStudent) {
            return view('assistance.validate', [
                'extend' => $this->extend,
                'success' => false,
                'message' => 'Código no enviado',
                'data' => null
            ]);
        }

        // 2. Buscar estudiante
        $student = Student::whereHas('person', function ($query) use ($dniStudent) {
            $query->where('identify_number', $dniStudent);
        })->first();

        if (!$student) {
            return view('assistance.validate', [
                'extend' => $this->extend,
                'success' => false,
                'message' => 'Estudiante no encontrado',
                'data' => null
            ]);
        }

        // 3. Sesión activa del día
        $assistanceSession = AssistanceSession::with('schedule')
            ->whereDate('date', Carbon::today())
            ->whereNull('time_ending')
            ->first();

        if (!$assistanceSession) {
            return view('assistance.validate', [
                'extend' => $this->extend,
                'success' => false,
                'message' => 'No existe sesión activa hoy',
                'data' => null
            ]);
        }

        // 4. Validar horario
        $now = Carbon::now()->format('H:i:s');

        if (
            $assistanceSession->schedule->time_start > $now ||
            $assistanceSession->schedule->time_end < $now
        ) {
            if (!$late) {
                return view('assistance.validate', [
                    'extend' => $this->extend,
                    'success' => false,
                    'message' => 'Fuera del horario permitido',
                    'data' => null
                ]);
            }
        }

        // 5. Evitar doble asistencia
        $exists = Assistance::where(
            'codassistance_session',
            $assistanceSession->codassistance_session
        )
            ->where('codstudent', $student->codstudent)
            ->exists();

        if ($exists) {
            return view('assistance.validate', [
                'extend' => $this->extend,
                'success' => true,
                'message' => 'Asistencia ya registrada',
                'data' => null
            ]);
        }

        // 6. Registrar asistencia
        $attendance = Assistance::create([
            'codassistance_session' => $assistanceSession->codassistance_session,
            'codstudent' => $student->codstudent,
            'time_entry' => Carbon::now(),
            'status' => $late ? 'late' : 'present',
            'observation' => $late ? 'Registro tardío' : null,
        ]);

        return view('assistance.validate', [
            'extend' => $this->extend,
            'success' => true,
            'message' => 'Asistencia registrada correctamente',
            'data' => $attendance
        ]);
    }

    public function take()
    {
        return view('assistance.take', [
            'extend' => $this->extend
        ]);
    }

    public function validateAttendance(Request $request)
    {
        $dniStudent = $request->dni;
        $late = $request->late === 'late';

        if (!$dniStudent) {
            return response()->json([
                'success' => false,
                'message' => 'Código no enviado'
            ]);
        }

        $student = Student::whereHas('person', function ($q) use ($dniStudent) {
            $q->where('identify_number', $dniStudent);
        })->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ]);
        }

        $assistanceSession = AssistanceSession::with('schedule')
            ->whereDate('date', Carbon::today())
            ->whereNull('time_ending')
            ->first();

        if (!$assistanceSession) {
            return response()->json([
                'success' => false,
                'message' => 'No existe sesión activa hoy'
            ]);
        }

        $now = Carbon::now()->format('H:i:s');

        if (
            $assistanceSession->schedule->time_start > $now ||
            $assistanceSession->schedule->time_end < $now
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Fuera del horario permitido'
            ]);
        }

        $exists = Assistance::where(
            'codassistance_session',
            $assistanceSession->codassistance_session
        )
            ->where('codstudent', $student->codstudent)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => true,
                'message' => 'Asistencia ya registrada'
            ]);
        }

        $attendance = Assistance::create([
            'codassistance_session' => $assistanceSession->codassistance_session,
            'codstudent' => $student->codstudent,
            'time_entry' => Carbon::now(),
            'status' => 'present',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada correctamente',
            'student' => $student->codstudent,
            'time' => $attendance->time_entry
        ]);
    }
}
