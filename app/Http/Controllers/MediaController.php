<?php

namespace App\Http\Controllers;

use App\Models\System\Justification;
use App\Models\System\Enrollment;
use App\Models\System\AssistanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JustificationController extends Controller
{
    public $extend = null;
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
    }

    public function index()
    {
        $data = Justification::with([
            'assistance_session',
            'enrollment'
        ])
        ->orderByDesc('codjustification')
        ->limit($this->perPage)
        ->get();

        return view('justification.list', [
            'extend' => $this->extend,
            'data' => $data,
            'assistance_sessions' => AssistanceSession::get(),
            'enrollments' => Enrollment::get(),
        ]);
    }

    public function form(Request $request, $id = null)
    {
        $justification = $id
            ? Justification::findOrFail($id)
            : null;

        $this->extend['view'] = 'form';

        return view('justification.form', [
            'extend' => $this->extend,
            'justification' => $justification,
            'assistance_sessions' => AssistanceSession::get(),
            'enrollments' => Enrollment::get(),
            'redirect' => $request->get('redirect')
        ]);
    }

    public function store(Request $request, $id = null)
    {
        $rules = [
            'codenrollment' => [
                'required',
                Rule::exists('system.enrollment', 'codenrollment')
            ],
            'codassistance_session' => [
                'nullable',
                Rule::exists('system.assistance_session', 'codassistance_session')
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
                'codenrollment' => $request->codenrollment,
                'codassistance_session' => $request->codassistance_session,
                'coduser_responsible' => Auth::id(),
                'type' => $request->type,
                'reason' => $request->reason,
            ];

            if ($id) {
                $justification = Justification::findOrFail($id);
                $justification->update($data);
                $message = 'Justificación actualizada correctamente';
            } else {
                $justification = Justification::create($data);
                $message = 'Justificación registrada correctamente';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $justification,
                'totalRecords' => Justification::count(),
                'redirect' => $request->redirect ?? route('justification.list')
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la justificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function records(Request $request, $from, $to, $keyword = null)
    {
        $query = Justification::with([
            'enrollment',
            'assistance_session'
        ])
        ->orderByDesc('codjustification');

        if (!empty($keyword) && $keyword !== 'null') {
            $query->where('reason', 'ILIKE', "%{$keyword}%");
        }

        if ($request->codenrollment) {
            $query->where('codenrollment', $request->codenrollment);
        }

        if ($request->codassistance_session) {
            $query->where('codassistance_session', $request->codassistance_session);
        }

        $total = (clone $query)->count();

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

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query = Justification::with('enrollment')
            ->orderByDesc('codjustification');

        if (!empty($keyword)) {
            $query->where('reason', 'ILIKE', "%{$keyword}%");
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

            $justification = Justification::findOrFail($id);
            $justification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Justificación eliminada correctamente',
                'totalRecords' => Justification::count()
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}