<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\System\Enrollment;
use App\Models\System\Assistance;
use Illuminate\Http\Request;
use Carbon\Carbon;


class BotController extends Controller
{
    public function __construct()
    {
        // Verificar token secreto
        $this->middleware(function ($request, $next) {
            $token = $request->header('Authorization');
            if ($token !== 'Bearer ' . env('BOT_API_TOKEN')) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
            }
            return $next($request);
        });
    }

    public function findByDni($dni)
    {
        try {
            $enrollment = Enrollment::with(['student.person', 'grade_schedule.grade'])
                ->whereHas('student.person', fn($q) => $q->where('identify_number', $dni))
                ->whereHas('period', fn($q) => $q->where('is_active', 'Y'))
                ->first();

            if (!$enrollment) {
                return response()->json(['success' => false, 'message' => 'Estudiante no encontrado']);
            }

            // Ver si asistió hoy
            $attendance = Assistance::where('codenrollment', $enrollment->codenrollment)
                ->whereHas('assistance_session', fn($q) => $q->whereDate('date', Carbon::today()))
                ->first();

            return response()->json([
                'success' => true,
                'student' => [
                    'name'       => ($enrollment->student->person->firstname ?? 'Sin Nombre') . ' ' .
                        ($enrollment->student->person->lastname_father ?? ''),
                    'grade'      => $enrollment->grade_schedule->grade->name_large ?? 'N/A',
                    'attendance' => $attendance ? '✅ ' . $attendance->status : '❌ No registrada',
                ]
            ]);
        } catch (\Exception $e) {
            // Esto te permitirá ver el error real en la consola del bot
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
