<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    public array $extend = [
        'title'      => 'Consulta de Asistencias',
        'controller' => 'guest',
    ];

    public function index()
    {
        // Solo necesitamos los periodos para el filtro del panel (step 3).
        // El resto de la lógica se resuelve vía API (GuestApiController).
        $periods = DB::table('system.period')
            ->whereNull('deleted_at')
            ->orderByDesc('is_active')
            ->orderByDesc('codperiod')
            ->select('codperiod', 'name', 'is_active')
            ->get();

        return view('guest.index', [
            'extend'  => $this->extend,
            'periods' => $periods,
        ]);
    }
}