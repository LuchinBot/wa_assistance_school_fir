<?php

namespace App\Exports;

use App\Models\System\Assistance;
use App\Models\System\Student;
use App\Models\System\AssistanceSession;
use App\Models\System\Enrollment;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssistanceExport implements WithMultipleSheets
{
    protected $sessionId;
    protected $gradeId;
    protected $gradeScheduleId;
    protected $keyword;
    protected $tab;
    protected $period;

    public function __construct(
        $sessionId       = null,
        $gradeId         = null,
        $keyword         = null,
        $gradeScheduleId = null,
        $tab             = 'present',
        $period          = null

    ) {
        $this->sessionId       = $sessionId;
        $this->gradeId         = $gradeId;
        $this->keyword         = $keyword;
        $this->gradeScheduleId = $gradeScheduleId;
        $this->tab             = $tab;
        $this->period             = $period;
    }

    public function sheets(): array
    {
        $sheets = [
            new AssistancePresentSheet(
                $this->sessionId,
                $this->gradeId,
                $this->gradeScheduleId,
                $this->keyword,
                $this->period
            ),
        ];

        if ($this->sessionId) {
            $sheets[] = new AssistanceAbsentSheet(
                $this->sessionId,
                $this->gradeId,
                $this->gradeScheduleId,
                $this->period
            );
        }

        return $sheets;
    }
}


/* =============================================
   TRAIT: Header institucional reutilizable
   Layout:
     Fila 1 → barra de acento (delgada)
     Fila 2 → [logo colegio] | [nombre institución] | [logo sistema]
     Fila 3 → [           ] | [subtítulo sistema  ] | [            ]
     Fila 4 → barra de acento (delgada)
     Fila 5 → título del reporte (fondo acento)
     Fila 6 → fecha generado
     Fila 7 → separador blanco
============================================= */
trait InstitutionHeader
{
    protected function insertHeader(
        Worksheet $sheet,
        string $lastCol,
        string $reportTitle,
        string $accentArgb
    ): void {
        $sheet->insertNewRowBefore(1, 7);

        // Alturas
        $sheet->getRowDimension(1)->setRowHeight(6);
        $sheet->getRowDimension(2)->setRowHeight(32);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(6);
        $sheet->getRowDimension(5)->setRowHeight(26);
        $sheet->getRowDimension(6)->setRowHeight(15);
        $sheet->getRowDimension(7)->setRowHeight(8);

        // ── Calcular columnas ────────────────────────────────────────
        // A-B  → logo colegio
        // C-penúltima → texto institución
        // última → logo sistema
        $allCols   = $this->colRange('A', $lastCol);
        $totalCols = count($allCols);
        $midStart  = 'C';
        $midEnd    = $totalCols >= 4 ? $allCols[$totalCols - 2] : $allCols[$totalCols - 1];

        // Merges
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:B3");                        // logo colegio
        $sheet->mergeCells("{$midStart}2:{$midEnd}2");      // nombre institución
        $sheet->mergeCells("{$midStart}3:{$midEnd}3");      // subtítulo
        $sheet->mergeCells("{$lastCol}2:{$lastCol}3");      // logo sistema
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->mergeCells("A6:{$lastCol}6");
        $sheet->mergeCells("A7:{$lastCol}7");

        // ── Fondo blanco en bloque 1-4 ───────────────────────────────
        $sheet->getStyle("A1:{$lastCol}4")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
        ]);

        // Barras de acento fila 1 y 4
        foreach ([1, 4] as $r) {
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $accentArgb]],
            ]);
        }

        // Borde sutil alrededor del bloque 1-4
        $sheet->getStyle("A1:{$lastCol}4")->applyFromArray([
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']],
            ],
        ]);

        // ── Nombre institución ────────────────────────────────────────
        $sheet->setCellValue("{$midStart}2", 'I.E. FRANCISCO IZQUIERDO RÍOS');
        $sheet->getStyle("{$midStart}2")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 16,
                'name'  => 'Arial',
                'color' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->setCellValue("{$midStart}3", 'Assistance Control System

  ·  Assistance School');
        $sheet->getStyle("{$midStart}3")->applyFromArray([
            'font' => [
                'size'   => 12,
                'name'   => 'Arial',
                'italic' => false,
                'color'  => ['argb' => 'FF64748B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Celda logo sistema (centrar texto vacío, la imagen va sobre ella)
        $sheet->getStyle("{$lastCol}2")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // ── Fila 5 y 6: título + metadatos en una sola celda ─────────────
        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->setCellValue('A5', mb_strtoupper($reportTitle) . '     |     Generado el ' . Carbon::now()->format('d/m/Y') . ' a las ' . Carbon::now()->format('H:i') . ' hrs.');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => [
                'bold'   => true,
                'size'   => 10,
                'name'   => 'Arial',
                'italic' => false,
                'color'  => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $accentArgb]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(24);

        // ── Fila 7: separador ─────────────────────────────────────────
        // $sheet->getStyle("A7:{$lastCol}7")->applyFromArray([
        //     'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
        // ]);

        // ── Logo colegio (izquierda) ──────────────────────────────────
        $logoSchool = public_path('img/logo_school.png');
        if (file_exists($logoSchool)) {
            $d1 = new Drawing();
            $d1->setName('Logo Colegio');
            $d1->setDescription('I.E. Francisco Izquierdo Ríos');
            $d1->setPath($logoSchool);
            $d1->setHeight(60);
            $d1->setCoordinates('A2');
            $d1->setOffsetX(10);
            $d1->setOffsetY(6);
            $d1->setWorksheet($sheet);
        }

        // ── Logo Assistance School (derecha) ──────────────────────────
        $logoSys = public_path('img/logo.png');
        if (file_exists($logoSys)) {
            $d2 = new Drawing();
            $d2->setName('Assistance School');
            $d2->setDescription('Assistance School');
            $d2->setPath($logoSys);
            $d2->setHeight(44);
            $d2->setCoordinates("{$lastCol}2");
            $d2->setOffsetX(4);
            $d2->setOffsetY(10);
            $d2->setWorksheet($sheet);
        }
    }

    /** Retorna array de columnas entre $from y $to, ej: A..J */
    private function colRange(string $from, string $to): array
    {
        $cols = [];
        $current = strtoupper($from);
        $end     = strtoupper($to);
        while (true) {
            $cols[] = $current;
            if ($current === $end) break;
            $current = $this->nextCol($current);
            if (strlen($current) > strlen($end) + 1) break; // seguridad
        }
        return $cols;
    }

    private function nextCol(string $col): string
    {
        $col = strtoupper($col);
        $len = strlen($col);
        for ($i = $len - 1; $i >= 0; $i--) {
            if ($col[$i] !== 'Z') {
                return substr($col, 0, $i) . chr(ord($col[$i]) + 1) . str_repeat('A', $len - $i - 1);
            }
        }
        return str_repeat('A', $len + 1);
    }
}


/* =============================================
   HOJA 1: PRESENTES / TARDANZAS
============================================= */
class AssistancePresentSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    use InstitutionHeader;

    protected $sessionId;
    protected $gradeId;
    protected $gradeScheduleId;
    protected $keyword;
    protected $period;

    const CYAN       = 'FF00B0CA';
    const GREEN      = 'FF8DC63F';
    const WHITE      = 'FFFFFFFF';
    const DARK       = 'FF1E293B';
    const ROW_ALT    = 'FFF0FBFD';
    const ROW_BORDER = 'FFD4EEF3';

    public function __construct($sessionId, $gradeId, $gradeScheduleId, $keyword, $period)
    {
        $this->sessionId       = $sessionId;
        $this->gradeId         = $gradeId;
        $this->gradeScheduleId = $gradeScheduleId;
        $this->keyword         = $keyword;
        $this->period          = $period;
    }

    public function title(): string
    {
        return 'Asistencias';
    }

    public function query()
    {
        $query = Assistance::with(
            'enrollment.student.person',
            'enrollment.grade_schedule.grade',
            'enrollment.period',
            'assistance_session.schedule'
        )->orderBy('codassistance', 'DESC');

        if (!empty($this->sessionId)) {
            $query->where('codassistance_session', $this->sessionId);
        }

        if (!empty($this->gradeScheduleId)) {
            $query->whereHas('enrollment', fn($q) => $q->where('codgrade_schedule', $this->gradeScheduleId));
        } elseif (!empty($this->gradeId)) {
            $query->whereHas('enrollment.grade_schedule', fn($q) => $q->where('codgrade', $this->gradeId));
        }

        // Filtro período
        if (!empty($this->period)) {
            $query->whereHas('enrollment', fn($q) => $q->where('codperiod', $this->period));
        } else {
            // Por defecto solo período activo
            $query->whereHas('enrollment.period', fn($q) => $q->where('is_active', 'Y'));
        }

        if (!empty($this->keyword)) {
            $query->whereHas('enrollment.student.person', function ($p) {
                $p->where('firstname', 'ILIKE', "%{$this->keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$this->keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$this->keyword}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return ['#', 'DNI', 'Nombre completo', 'Teléfono', 'Grado', 'Sección', 'Periodo', 'Sesión', 'Hora ingreso', 'Estado', 'Observación'];
    }
    public function map($row): array
    {
        static $i = 0;
        $i++;

        $statusMap  = [
            'present'   => 'Presente',
            'late'      => 'Tardanza',
            'absent'    => 'Ausente',
            'justified' => 'Justificado',
        ];
        $enrollment = $row->enrollment;
        $gs         = $enrollment?->grade_schedule;
        $person     = $enrollment?->student?->person;

        return [
            $i,
            $person?->identify_number ?? '-',
            trim(
                ($person?->firstname       ?? '') . ' ' .
                    ($person?->lastname_father ?? '') . ' ' .
                    ($person?->lastname_mom    ?? '')
            ),
            $person?->phone ?? '-',
            $gs?->grade?->name_large ?? '-',
            $gs?->section ?? '-',
            $enrollment?->period?->name ?? '-',  // ← período
            ($row->assistance_session?->schedule?->turn ?? '-') . ' · ' .
                \Carbon\Carbon::parse($row->assistance_session?->date)->format('d/m/Y'),
            $row->time_entry ? Carbon::parse($row->time_entry)->format('H:i:s') : '-',
            $statusMap[$row->status] ?? $row->status,
            $row->observation ?? '',
        ];
    }

    public function columnWidths(): array
    {
        // Agregamos columna Periodo (K), corremos Observación a K
        return [
            'A' => 6,
            'B' => 14,
            'C' => 32,
            'D' => 16,
            'E' => 20,
            'F' => 10,
            'G' => 18,  // Periodo
            'H' => 28,  // Sesión
            'I' => 14,  // Hora
            'J' => 13,  // Estado
            'K' => 30,  // Observación
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'K'; // ← era 'J', ahora 'K' por columna Periodo

                $this->insertHeader($sheet, $lastCol, 'Reporte de Asistencias', self::CYAN);

                $headerRow = 8;
                $lastRow   = $sheet->getHighestRow();

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::WHITE], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::GREEN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => [
                        'top'    => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF6AAF1A']],
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF5A9F10']],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(22);

                for ($i = $headerRow + 1; $i <= $lastRow; $i++) {
                    $isEven = ($i % 2 === 0);
                    $sheet->getStyle("A{$i}:{$lastCol}{$i}")->applyFromArray([
                        'font'      => ['size' => 9, 'name' => 'Arial', 'color' => ['argb' => self::DARK]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $isEven ? self::ROW_ALT : self::WHITE]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::ROW_BORDER]]],
                    ]);
                    foreach (['A', 'B', 'D', 'F', 'G', 'I', 'J'] as $col) {
                        $sheet->getStyle("{$col}{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    $sheet->getRowDimension($i)->setRowHeight(18);
                }

                // Colores estado — ahora en columna J
                for ($i = $headerRow + 1; $i <= $lastRow; $i++) {
                    $status = $sheet->getCell("J{$i}")->getValue();
                    [$fg, $bg] = match ($status) {
                        'Presente'    => ['FF1B6B21', 'FFE8F5E9'],
                        'Tardanza'    => ['FF7C4D00', 'FFFFF3E0'],
                        'Ausente'     => ['FF8B1A1A', 'FFFFEBEE'],
                        'Justificado' => ['FF556B00', 'FFF3F9D2'],
                        default       => ['FF64748B', self::WHITE],
                    };
                    $sheet->getStyle("J{$i}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 8, 'name' => 'Arial', 'color' => ['argb' => $fg]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                    ]);
                }

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::CYAN]]],
                ]);

                $sheet->freezePane('A' . ($headerRow + 1));
                $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }
}


/* =============================================
   HOJA 2: AUSENTES
============================================= */
class AssistanceAbsentSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    use InstitutionHeader;

    protected $sessionId;
    protected $gradeId;
    protected $gradeScheduleId;
    protected $period;


    const RED_DARK   = 'FFD32F2F';
    const WHITE      = 'FFFFFFFF';
    const DARK       = 'FF1E293B';
    const ROW_ALT    = 'FFFFF8F8';
    const ROW_BORDER = 'FFFFCDD2';

    public function __construct($sessionId, $gradeId, $gradeScheduleId, $period)
    {
        $this->sessionId       = $sessionId;
        $this->gradeId         = $gradeId;
        $this->gradeScheduleId = $gradeScheduleId;
        $this->period          = $period;
    }

    public function title(): string
    {
        return 'Ausentes';
    }

    public function collection()
    {
        $session = AssistanceSession::find($this->sessionId);
        if (!$session) return collect();

        $query = Enrollment::with('student.person', 'grade_schedule.grade', 'period')
            ->whereHas('grade_schedule', fn($q) => $q->where('codschedule', $session->codschedule))
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('system.assistance')
                    ->whereColumn('assistance.codenrollment', 'system.enrollment.codenrollment')
                    ->where('assistance.codassistance_session', $this->sessionId)
                    ->whereNull('assistance.deleted_at');
            });

        // Filtro período
        if (!empty($this->period)) {
            $query->where('codperiod', $this->period);
        } else {
            $query->whereHas('period', fn($q) => $q->where('is_active', 'Y'));
        }

        if (!empty($this->gradeScheduleId)) {
            $query->where('codgrade_schedule', $this->gradeScheduleId);
        } elseif (!empty($this->gradeId)) {
            $query->whereHas('grade_schedule', fn($q) => $q->where('codgrade', $this->gradeId));
        }

        return $query->orderBy('codenrollment')->get();
    }

    public function headings(): array
    {
        return ['#', 'DNI', 'Nombre completo', 'Teléfono', 'Grado', 'Sección', 'Periodo'];
    }
    public function map($row): array
    {
        static $i = 0;
        $i++;

        $gs     = $row->grade_schedule;
        $person = $row->student?->person;

        return [
            $i,
            $person?->identify_number ?? '-',
            trim(
                ($person?->firstname       ?? '') . ' ' .
                    ($person?->lastname_father ?? '') . ' ' .
                    ($person?->lastname_mom    ?? '')
            ),
            $person?->phone ?? '-',
            $gs?->grade?->name_large ?? '-',
            $gs?->section ?? '-',
            $row->period?->name ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 14,
            'C' => 32,
            'D' => 16,
            'E' => 20,
            'F' => 12,
            'G' => 18,  // ← Periodo
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'G'; // ← era 'F', ahora 'G' por Periodo

                $this->insertHeader($sheet, $lastCol, 'Reporte de Ausentes', self::RED_DARK);

                $headerRow = 8;
                $lastRow   = $sheet->getHighestRow();

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::WHITE], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::RED_DARK]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF9A0007']]],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(22);

                for ($i = $headerRow + 1; $i <= $lastRow; $i++) {
                    $isEven = ($i % 2 === 0);
                    $sheet->getStyle("A{$i}:{$lastCol}{$i}")->applyFromArray([
                        'font'      => ['size' => 9, 'name' => 'Arial', 'color' => ['argb' => self::DARK]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $isEven ? self::ROW_ALT : self::WHITE]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::ROW_BORDER]]],
                    ]);
                    foreach (['A', 'B', 'D', 'F', 'G'] as $col) {
                        $sheet->getStyle("{$col}{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    $sheet->getRowDimension($i)->setRowHeight(18);
                }

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::RED_DARK]]],
                ]);

                $sheet->freezePane('A' . ($headerRow + 1));
                $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }
}
