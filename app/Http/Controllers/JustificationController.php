<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Grade;
use App\Models\Person;
use App\Models\Profession;
use App\Models\System\Assignee;
use App\Models\System\GradeSchedule;
use App\Models\System\Student;
use App\Models\System\Media;
use App\Models\System\Enrollment;
use App\Models\System\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Carbon\Carbon;
use App\Exports\StudentExport;
use App\Models\System\Assistance;
use App\Models\System\AssistanceSession;
use App\Models\System\Justification;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class JustificationController extends Controller
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
            'title' => 'Justificaciones',
            'title_form' => 'justificación',
            'view' => 'list',
            'controller' => 'justification',
            'totalRecord' => Justification::count(),
        ];
        $this->keyword = null;
    }

    public function index()
    {
        $user = Auth::user();
        $data = Justification::with([
            'assistance_session',
            'enrollment',
        ])
            ->orderByDesc('codjustification')
            ->limit($this->perPage)
            ->get();

        $assistance_sessions = AssistanceSession::get();
        $enrollments = Enrollment::get();

        return view('justification.list', [
            'extend' => $this->extend,
            'data' => $data,
            'assistance_sessions' => $assistance_sessions,
            'enrollments' => $enrollments,
        ]);
    }
    public function form(Request $request, $id = null)
    {
        $user = Auth::user();

        $justification = $id
            ? Justification::with([
                'assistance_session',
                'enrollment'
            ])->findOrFail($id)
            : null;

        $assistance_sessions = AssistanceSession::get();
        $enrollments = Enrollment::with('student.person')->get();

        $this->extend['view'] = 'form';

        return view('justification.form', [
            'extend' => $this->extend,
            'justification' => $justification,
            'assistance_sessions' => $assistance_sessions,
            'enrollments' => $enrollments,
            'redirect' => $request->get('redirect')
        ]);
    }

    public function store(Request $request, $id = null)
    {
        $rules = [
            'codenrollment' => [
                'required',
                Rule::exists('enrollment', 'codenrollment')
            ],
            'codassistance_session' => [
                'nullable',
                Rule::exists('assistance_session', 'codassistance_session')
            ],
            'type' => [
                'required',
                Rule::in(['JT', 'JI'])
            ],
            'reason' => [
                'required',
                'string'
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $data = [
                'codenrollment'           => $request->codenrollment,
                'codassistance_session'  => $request->codassistance_session,
                'coduser_responsible'    => Auth::user()->coduser,
                'type'                   => $request->type,
                'reason'                 => $request->reason,
            ];

            if ($id) {
                // UPDATE
                $justification = Justification::findOrFail($id);
                $justification->update($data);

                $message = 'Justificación actualizada correctamente';
            } else {
                // CREATE
                $justification = Justification::create($data);

                $message = 'Justificación registrada correctamente';
            }

            // APLICAR JUSTIFICACIÓN A SESIÓN ACTIVA
            // 🔥 OBTENER EL ENROLLMENT CON SU HORARIO
            $enrollment = Enrollment::with('grade_schedule')
                ->find($justification->codenrollment);

            if ($enrollment && $enrollment->grade_schedule) {

                $codschedule = $enrollment->grade_schedule->codschedule;

                // 🔥 OBTENER SESIÓN SEGÚN TIPO
                if ($justification->type === 'JI') {

                    // 👉 última sesión de ese horario
                    $session = AssistanceSession::where('codschedule', $codschedule)
                        ->orderByDesc('date')
                        ->orderByDesc('time_opening')
                        ->first();
                } else {

                    // 👉 JT usa sesión específica
                    $session = AssistanceSession::where('codassistance_session', $justification->codassistance_session)
                        ->where('codschedule', $codschedule)
                        ->first();
                }

                // 🔥 SOLO SI HAY SESIÓN VÁLIDA
                if ($session) {

                    $attendance = Assistance::where('codassistance_session', $session->codassistance_session)
                        ->where('codenrollment', $justification->codenrollment)
                        ->lockForUpdate()
                        ->first();

                    if ($attendance) {

                        if ($attendance->status !== 'justified') {
                            $attendance->update([
                                'status' => 'justified',
                                'observation' => 'Actualizado por justificación: ' . $justification->reason,
                            ]);
                        }
                    } else {

                        Assistance::create([
                            'codassistance_session' => $session->codassistance_session,
                            'codenrollment'         => $justification->codenrollment,
                            'coduser_responsible'   => Auth::user()->coduser,
                            'time_entry'            => now(),
                            'status'                => 'justified',
                            'observation'           => 'Justificación aplicada: ' . $justification->reason,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $justification,
                'redirect' => $request->redirect ?? route('justification.list')
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error guardando justificación', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la justificación'
            ], 500);
        }
    }

    public function records(Request $request, $from, $to, $keyword = null)
    {
        $query = Justification::with([
            'enrollment.student.person',
            'assistance_session',
            'user',
        ])->orderByDesc('codjustification');

        // BUSCADOR por nombre del estudiante o tipo
        if (!empty($keyword) && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->where('type', 'ILIKE', "%{$keyword}%")
                    ->orWhere('reason', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('enrollment.student.person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        // FILTRO por tipo (JT / JI)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // FILTRO por sesión de asistencia
        if ($request->filled('codassistance_session')) {
            $query->where('codassistance_session', $request->codassistance_session);
        }

        // FILTRO por enrollment (estudiante específico)
        if ($request->filled('codenrollment')) {
            $query->where('codenrollment', $request->codenrollment);
        }

        $total = (clone $query)->count();

        $data = $query
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'total'   => $total,
            'from'    => $from,
            'to'      => $to,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query = Justification::with([
            'enrollment.student.person',
            'assistance_session',
            'user',
        ])->orderByDesc('codjustification');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('type', 'ILIKE', "%{$keyword}%")
                    ->orWhere('reason', 'ILIKE', "%{$keyword}%")
                    ->orWhereHas('enrollment.student.person', function ($p) use ($keyword) {
                        $p->where('firstname', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                            ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%");
                    });
            });
        }

        $total = (clone $query)->count();

        $data = $query->limit($this->perPage)->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'total'   => $total,
            'keyword' => $keyword,
        ]);
    }

    public function destroy($id)
    {
        try {
            $justification = Justification::findOrFail($id);
            $justification->delete();

            return response()->json([
                'success'      => true,
                'message'      => 'Justificación eliminada correctamente',
                'totalRecords' => Justification::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
