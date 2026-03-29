<?php

namespace App\Http\Controllers;

use App\Models\System\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TCPDF;

class CarnetPDFController extends Controller
{
    private array $cache = [];

    /*
    =====================================================
      MEDIDAS DE LA CUADRÍCULA
      QR image: 28mm ancho × 30mm alto  (QR cuadrado + franja DNI)
      Espaciado: 2mm horizontal, 2mm vertical
      Margen página: 5mm todos los lados

      Resultado por página A4 (210 × 297mm):
        Columnas : floor((210 - 10) / 30) = 6
        Filas    : floor((297 - 10) / 32) = 9
        Total    : 54 QRs por página
    =====================================================
    */
    private const QR_W      = 28;   // mm ancho de la imagen del carnet
    private const QR_H      = 32;   // mm alto  de la imagen del carnet (QR + DNI)
    private const GAP_X     = 2;    // mm espacio horizontal entre QRs
    private const GAP_Y     = 2;    // mm espacio vertical entre QRs
    private const MARGIN    = 5;    // mm margen de página (todos los lados)
    private const PAGE_W    = 210;  // mm A4 ancho
    private const PAGE_H    = 297;  // mm A4 alto

    /* =====================================================
       INDIVIDUAL
    ===================================================== */

    public function generateCarnet($id)
    {
        try {
            $student = Student::with([
                'person',
                'currentEnrollment.grade_schedule.grade.level',
                'currentEnrollment.grade_schedule.schedule',
            ])->findOrFail($id);

            $pdf = $this->createPdf();
            $pdf->AddPage();
            $this->placeQr($pdf, $student, self::MARGIN, self::MARGIN);

            return $this->pdfResponse(
                $pdf,
                'carnet_' . $student->person->identify_number . '.pdf'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar carnet: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =====================================================
       MASIVO
    ===================================================== */

    public function generateMassiveCarnets(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        try {
            $query = Student::with([
                'person',
                'currentEnrollment.grade_schedule.grade.level',
                'currentEnrollment.grade_schedule.schedule',
            ]);

            if ($request->select_all) {

                if ($request->search) {
                    $keyword = $request->search;
                    $query->where(function ($q) use ($keyword) {
                        $q->where('codstudent', 'ILIKE', "%{$keyword}%")
                            ->orWhereHas('person', function ($p) use ($keyword) {
                                $p->where('firstname', 'ILIKE', "%{$keyword}%")
                                    ->orWhere('lastname_father', 'ILIKE', "%{$keyword}%")
                                    ->orWhere('lastname_mom', 'ILIKE', "%{$keyword}%");
                            });
                    });
                }

                if ($request->codgrade_schedule) {
                    $query->whereHas('currentEnrollment', function ($q) use ($request) {
                        $q->where('codgrade_schedule', $request->codgrade_schedule);
                    });
                }
            } else {
                $ids = json_decode($request->ids, true);
                if (!$ids || !is_array($ids)) {
                    return response()->json(['success' => false, 'message' => 'IDs inválidos'], 400);
                }
                $query->whereIn('codstudent', $ids);
            }

            if (!$query->exists()) {
                return response()->json(['success' => false, 'message' => 'Sin estudiantes'], 404);
            }

            // ── Calcular cuántas columnas y filas caben en A4 ──
            $cols    = (int) floor((self::PAGE_W - 2 * self::MARGIN + self::GAP_X) / (self::QR_W + self::GAP_X));
            $rows    = (int) floor((self::PAGE_H - 2 * self::MARGIN + self::GAP_Y) / (self::QR_H + self::GAP_Y));
            $perPage = $cols * $rows;

            // Centrar el bloque en la página
            $blockW = $cols * self::QR_W + ($cols - 1) * self::GAP_X;
            $blockH = $rows * self::QR_H + ($rows - 1) * self::GAP_Y;
            $startX = (self::PAGE_W - $blockW) / 2;
            $startY = (self::PAGE_H - $blockH) / 2;

            $pdf   = $this->createPdf();
            $index = 0;

            $query->orderBy('codstudent')
                ->chunk(100, function ($students) use (
                    &$index, $pdf, $cols, $perPage,
                    $startX, $startY
                ) {
                    foreach ($students as $student) {

                        if ($index % $perPage === 0) {
                            $pdf->AddPage();
                        }

                        $pos = $index % $perPage;
                        $col = $pos % $cols;
                        $row = (int) floor($pos / $cols);

                        $x = $startX + $col * (self::QR_W + self::GAP_X);
                        $y = $startY + $row * (self::QR_H + self::GAP_Y);

                        $this->placeQr($pdf, $student, $x, $y);

                        $index++;

                        if ($index % 50 === 0) {
                            gc_collect_cycles();
                        }
                    }
                });

            return $this->pdfResponse(
                $pdf,
                'carnets_' . now()->format('Ymd_His') . '.pdf'
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* =====================================================
       PDF CORE
    ===================================================== */

    private function createPdf(): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator(config('app.name'));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        return $pdf;
    }

    private function pdfResponse(TCPDF $pdf, string $filename)
    {
        return response(
            $pdf->Output($filename, 'S'),
            200
        )->header('Content-Type', 'application/pdf');
    }

    /* =====================================================
       COLOCAR UN QR EN EL PDF
    ===================================================== */

    /**
     * Coloca la imagen del carnet (QR + DNI) en la posición indicada.
     * Si no hay imagen guardada, dibuja un recuadro con el DNI como fallback.
     */
    private function placeQr(TCPDF $pdf, $student, float $x, float $y): void
    {
        $url       = data_get($student, 'carnet_url');
        $localPath = $this->resolveLocalPath($url);

        if ($localPath) {
            $pdf->Image(
                $localPath,
                $x,
                $y,
                self::QR_W,
                self::QR_H,
                '',   // tipo auto
                '',   // link
                '',   // dirección
                false,
                300,  // DPI
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );
        } else {
            // Fallback: recuadro + DNI en texto si no hay imagen
            $dni = data_get($student, 'person.identify_number', '—');

            $pdf->SetDrawColor(180, 180, 180);
            $pdf->Rect($x, $y, self::QR_W, self::QR_H);

            $pdf->SetFont('helvetica', '', 6);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetXY($x, $y + (self::QR_H / 2) - 2);
            $pdf->Cell(self::QR_W, 4, $dni, 0, 0, 'C');
        }
    }

    /* =====================================================
       IMÁGENES — descarga y cachea en temp
    ===================================================== */

    private function resolveLocalPath(?string $url): ?string
    {
        if (!$url) return null;

        if (isset($this->cache[$url])) {
            return $this->cache[$url];
        }

        try {
            $ext  = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
            $temp = storage_path('app/temp/' . md5($url) . '.' . $ext);

            if (!file_exists($temp)) {
                $response = Http::timeout(20)->get($url);

                if (!$response->successful()) {
                    return null;
                }

                if (!is_dir(dirname($temp))) {
                    mkdir(dirname($temp), 0755, true);
                }

                file_put_contents($temp, $response->body());
            }

            $this->cache[$url] = $temp;
            return $temp;
        } catch (\Exception $e) {
            Log::error('Error descargando imagen carnet', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}