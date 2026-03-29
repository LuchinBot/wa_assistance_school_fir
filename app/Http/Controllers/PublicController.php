<?php

namespace App\Http\Controllers;

use App\Models\System\Journalist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PublicController extends Controller
{
    public $extend = null;

    public function __construct()
    {
        $this->extend = [
            'title' => 'Estadísticas Públicas',
            'controller' => 'statistics',
        ];
    }

    public function index()
    {
        return view('public.home');
    }

    public function statistics()
    {
        return view('public.statistics', array_merge(['extend' => $this->extend]));
    }

    public function getStatistics(Request $request)
    {
        try {
            $type = $request->get('type', 'general');

            Log::info('Solicitando estadísticas', ['type' => $type]);

            switch ($type) {
                case 'general':
                    $data = $this->getGeneralStats();
                    break;
                case 'filial':
                    $data = $this->getFilialStats();
                    break;
                case 'age':
                    $data = $this->getAgeStats();
                    break;
                case 'year':
                    $data = $this->getYearStats();
                    break;
                default:
                    return response()->json(['error' => 'Tipo no válido'], 400);
            }

            Log::info('Estadísticas generadas', ['type' => $type, 'data' => $data]);
            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('Error en getStatistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    private function getGeneralStats()
    {
        $total = Journalist::count();
        $habilitados = Journalist::where('affiliation_status', 'Y')->count();
        $inhabilitados = Journalist::where('affiliation_status', 'N')->count();

        return [
            'total' => $total,
            'habilitados' => $habilitados,
            'inhabilitados' => $inhabilitados,
            'porcentaje_habilitados' => $total > 0 ? round(($habilitados / $total) * 100, 2) : 0,
        ];
    }

    private function getFilialStats()
    {
        $stats = Journalist::select(
            'system.journalist.codfilial',
            'main.filial.name_large',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN affiliation_status = 'Y' THEN 1 ELSE 0 END) as habilitados"),
            DB::raw("SUM(CASE WHEN affiliation_status = 'N' THEN 1 ELSE 0 END) as inhabilitados")
        )
        ->join('main.filial', 'system.journalist.codfilial', '=', 'main.filial.codfilial')
        ->groupBy('system.journalist.codfilial', 'main.filial.name_large')
        ->orderBy('total', 'desc')
        ->get();

        return [
            'filiales' => $stats->pluck('name_large'),
            'totales' => $stats->pluck('total'),
            'habilitados' => $stats->pluck('habilitados'),
            'inhabilitados' => $stats->pluck('inhabilitados'),
        ];
    }

    private function getAgeStats()
    {
        $journalists = Journalist::with('person')
            ->whereHas('person', function($query) {
                $query->whereNotNull('birthday');
            })
            ->get();

        $ageRanges = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56-65' => 0,
            '65+' => 0,
        ];

        foreach ($journalists as $journalist) {
            if ($journalist->person && $journalist->person->birthday) {
                $age = Carbon::parse($journalist->person->birthday)->age;
                
                if ($age >= 18 && $age <= 25) $ageRanges['18-25']++;
                elseif ($age >= 26 && $age <= 35) $ageRanges['26-35']++;
                elseif ($age >= 36 && $age <= 45) $ageRanges['36-45']++;
                elseif ($age >= 46 && $age <= 55) $ageRanges['46-55']++;
                elseif ($age >= 56 && $age <= 65) $ageRanges['56-65']++;
                elseif ($age > 65) $ageRanges['65+']++;
            }
        }

        return [
            'rangos' => array_keys($ageRanges),
            'cantidades' => array_values($ageRanges),
        ];
    }

    private function getYearStats()
    {
        $stats = Journalist::select(
            DB::raw('EXTRACT(YEAR FROM affiliation_date) as year'),
            DB::raw('COUNT(*) as cantidad')
        )
        ->whereNotNull('affiliation_date')
        ->groupBy(DB::raw('EXTRACT(YEAR FROM affiliation_date)'))
        ->orderBy('year', 'asc')
        ->get();

        return [
            'years' => $stats->pluck('year'),
            'cantidades' => $stats->pluck('cantidad'),
        ];
    }
}