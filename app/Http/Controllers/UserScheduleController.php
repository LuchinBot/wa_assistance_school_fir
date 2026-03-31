<?php
// app/Http/Controllers/GradeScheduleController.php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Security\User;
use App\Models\System\GradeSchedule;
use App\Models\System\Schedules;
use App\Models\System\UserSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserScheduleController extends Controller
{
    public $extend = null;
    protected $perPage = 10;

    public function __construct()
    {
        $this->middleware('module.permission:listar')->only('index');
        $this->middleware('module.permission:crear')->only('store');
        $this->middleware('module.permission:eliminar')->only('destroy');

        $this->extend = [
            'title'       => 'Usuarios por Horarios',
            'title_form'  => 'Asignación de Horario a Usuario',
            'view' => 'list',
            'controller'  => 'user_schedule',
            'totalRecord' => UserSchedule::count(),
        ];
    }

    public function index()
    {
        $data = UserSchedule::with('user', 'schedule')
            ->limit($this->perPage)
            ->get();

        $users    = User::with('person')->orderBy('coduser')->get();
        $schedules = Schedules::orderBy('turn')->get();

        return view('user_schedule.list', [
            'extend'    => $this->extend,
            'data'      => $data,
            'users'    => $users,
            'schedules' => $schedules,
        ]);
    }

    public function form(Request $request, $id = null)
    {
        $user_schedule = $id ? UserSchedule::find($id) : null;
        $users    = User::with('person')->orderBy('coduser')->get();
        $schedules = Schedules::orderBy('turn')->get();

        $this->extend['view'] = 'form';

        return view('user_schedule.form', [
            'extend'    => $this->extend,
            'user_schedule'      => $user_schedule,
            'users'    => $users,
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'coduser' => ['required', 'integer', 'exists:user,coduser'],
            'codschedule' => [
                'required',
                'integer',
                'exists:schedules,codschedule',
                Rule::unique('user_schedule')
                    ->where(function ($query) use ($request) {
                        return $query->where('coduser', $request->coduser);
                    })
                    ->ignore($id, 'coduser_schedule') 
            ],
        ], [
            'codschedule.unique' => 'Este usuario ya tiene asignado este horario.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {

            $data = $request->except(['_token']);

            if ($id) {
                $modules = UserSchedule::findOrFail($id);
                $modules->update($data);
                $message = 'Registro actualizado exitosamente';
            } else {
                $modules = UserSchedule::create($data);
                $message = 'Registro creado exitosamente';
            }

            $data = UserSchedule::with('user.person', 'schedule')->get();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $data,
                'totalRecords' => UserSchedule::count(),
                'redirect' => route('user_schedule.list'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function records($from, $to, $keyword = 'null')
    {
        $query = UserSchedule::with('user.person', 'schedule')
            ->orderBy('coduser_schedule', 'DESC');

        if ($keyword && $keyword !== 'null') {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas(
                    'user.person',
                    fn($g) =>
                    $g->where('firstname', 'ILIKE', "%{$keyword}%")
                        ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                )->orWhereHas(
                    'schedule',
                    fn($s) =>
                    $s->where('turn', 'ILIKE', "%{$keyword}%")
                );
            });
        }

        $total = (clone $query)->count();
        $data  = $query->skip($from)->take($to - $from)->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'total'   => $total,
            'from'    => $from,
            'to'      => $to,
        ]);
    }

    public function destroy($id)
    {
        try {
            UserSchedule::findOrFail($id)->delete();

            return response()->json([
                'success'      => true,
                'message'      => 'Asignación eliminada correctamente',
                'totalRecords' => UserSchedule::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
