<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Actions\ConsultaLCS;



class ActionsController extends Controller
{

    public function person(Request $request)
    {
        $td = strtoupper(trim($request->td));
        $identifyNumber = $request->identify_number;

        $apiConsulta = new ConsultaLCS;

        if ($td === 'DNI') {
            $consulta = $apiConsulta->ConsultaDNI($identifyNumber);
        } elseif ($td === 'RUC') {
            //$consulta = $apiConsulta->ConsultaRUC($identifyNumber);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de documento no válido. ' . $td,
                'data' => null
            ]);
        }

        if (!$consulta) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró datos de la persona.',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Datos encontrados correctamente.',
            'data' => $consulta
        ]);
    }
}
