<?php

namespace App\Exports;

use App\Models\System\Student;
use App\Models\System\GradeSchedule;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;
use App\Models\System\Period;
// ── Reutilizamos el mismo trait del export de asistencia ──────────────
// Si está en el mismo archivo puedes copiarlo; si no, importa el trait.
// Aquí lo incluimos completo para que sea autocontenido.

trait StudentInstitutionHeader
{
    protected function insertHeader(Worksheet $sheet, string $lastCol, string $reportTitle, string $accentArgb): void
    {
        $sheet->insertNewRowBefore(1, 7);

        $sheet->getRowDimension(1)->setRowHeight(6);
        $sheet->getRowDimension(2)->setRowHeight(32);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(6);
        $sheet->getRowDimension(5)->setRowHeight(26);
        $sheet->getRowDimension(6)->setRowHeight(15);
        $sheet->getRowDimension(7)->setRowHeight(8);

        $allCols   = $this->colRange('A', $lastCol);
        $totalCols = count($allCols);
        $midStart  = 'C';
        $midEnd    = $totalCols >= 4 ? $allCols[$totalCols - 2] : $allCols[$totalCols - 1];

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:B3");
        $sheet->mergeCells("{$midStart}2:{$midEnd}2");
        $sheet->mergeCells("{$midStart}3:{$midEnd}3");
        $sheet->mergeCells("{$lastCol}2:{$lastCol}3");
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->mergeCells("A6:{$lastCol}6");
        $sheet->mergeCells("A7:{$lastCol}7");

        $sheet->getStyle("A1:{$lastCol}4")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
        ]);

        foreach ([1, 4] as $r) {
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $accentArgb]],
            ]);
        }

        $sheet->getStyle("A1:{$lastCol}4")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
        ]);

        $sheet->setCellValue("{$midStart}2", 'I.E. FRANCISCO IZQUIERDO RÍOS');
        $sheet->getStyle("{$midStart}2")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'name' => 'Arial', 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->setCellValue("{$midStart}3", 'Assistance Control System

  ·  Assistance School');
        $sheet->getStyle("{$midStart}3")->applyFromArray([
            'font'      => ['size' => 12, 'name' => 'Arial', 'color' => ['argb' => 'FF64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("{$lastCol}2")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A5', mb_strtoupper($reportTitle) . '     |     Generado el ' . Carbon::now()->format('d/m/Y') . ' a las ' . Carbon::now()->format('H:i') . ' hrs.');
        $sheet->getStyle('A5')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $accentArgb]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(24);

        $logoSchool = public_path('img/logo_school.png');
        if (file_exists($logoSchool)) {
            $d1 = new Drawing();
            $d1->setName('Logo Colegio')->setDescription('I.E. Francisco Izquierdo Ríos')
                ->setPath($logoSchool)->setHeight(60)
                ->setCoordinates('A2')->setOffsetX(10)->setOffsetY(6)
                ->setWorksheet($sheet);
        }

        $logoSys = public_path('img/logo.png');
        if (file_exists($logoSys)) {
            $d2 = new Drawing();
            $d2->setName('Assistance School')->setDescription('Assistance School')
                ->setPath($logoSys)->setHeight(44)
                ->setCoordinates("{$lastCol}2")->setOffsetX(4)->setOffsetY(10)
                ->setWorksheet($sheet);
        }
    }

    private function colRange(string $from, string $to): array
    {
        $cols = [];
        $current = strtoupper($from);
        $end     = strtoupper($to);
        while (true) {
            $cols[] = $current;
            if ($current === $end) break;
            $current = $this->nextCol($current);
            if (strlen($current) > strlen($end) + 1) break;
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


class StudentExport implements WithMultipleSheets
{
    protected $gradeScheduleId;
    protected $keyword;
    protected $periodId;

    public function __construct($gradeScheduleId = null, $keyword = null, $periodId = null)
    {
        $this->gradeScheduleId = $gradeScheduleId;
        $this->keyword         = $keyword;
        $this->periodId        = $periodId;
    }

    public function sheets(): array
    {
        return [
            new StudentSheet($this->gradeScheduleId, $this->keyword, $this->periodId),
        ];
    }
}


class StudentSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    use StudentInstitutionHeader;

    protected $gradeScheduleId;
    protected $keyword;
    protected $periodId;

    const CYAN       = 'FF00B0CA';
    const WHITE      = 'FFFFFFFF';
    const DARK       = 'FF1E293B';
    const ROW_ALT    = 'FFF0FBFD';
    const ROW_BORDER = 'FFD4EEF3';
    const HEADER_BG  = 'FF0891B2'; // cyan más oscuro para cabecera

    public function __construct($gradeScheduleId, $keyword, $periodId)
    {
        $this->gradeScheduleId = $gradeScheduleId;
        $this->keyword         = $keyword;
        $this->periodId        = $periodId;
    }

    public function title(): string
    {
        return 'Estudiantes';
    }

    public function query()
    {
        $query = Student::with([
            'person',
            'currentEnrollment.grade_schedule.grade',
            'currentEnrollment.grade_schedule.schedule'
        ])->orderBy('codstudent', 'DESC');

        if (!empty($this->periodId)) {
            $query->whereHas('currentEnrollment', function ($q) {
                $q->where('codperiod', $this->periodId);
            });
        }
        if (!empty($this->gradeScheduleId)) {
            $query->whereHas('enrollments', function ($q) {
                $q->where('codgrade_schedule', $this->gradeScheduleId);
            });
        }

        if (!empty($this->keyword)) {
            $query->whereHas('person', function ($p) {
                $p->where('firstname', 'ILIKE', "%{$this->keyword}%")
                    ->orWhere('lastname_father', 'ILIKE', "%{$this->keyword}%")
                    ->orWhere('lastname_mom', 'ILIKE', "%{$this->keyword}%")
                    ->orWhere('identify_number', 'ILIKE', "%{$this->keyword}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return ['#', 'DNI', 'Nombre completo', 'Teléfono', 'Grado', 'Sección', 'Turno', 'Fecha registro'];
    }

    public function map($row): array
    {
        static $i = 0;
        $i++;

        $gs = $row->currentEnrollment?->grade_schedule;

        return [
            $i,
            $row->person->identify_number ?? '-',
            trim(($row->person->firstname ?? '') . ' ' . ($row->person->lastname_father ?? '') . ' ' . ($row->person->lastname_mom ?? '')),
            $row->person->phone ?? '-',
            $gs?->grade?->name_large ?? '-',
            $gs?->section ?? '-',
            $gs?->schedule?->turn ?? '-',
            $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y') : '-',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 14, 'C' => 34, 'D' => 16, 'E' => 22, 'F' => 10, 'G' => 16, 'H' => 16];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $periodName = 'Todos los periodos';

                if ($this->periodId) {
                    $period = Period::find($this->periodId);
                    if ($period) {
                        $periodName = $period->name;
                    }
                }
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'H';

                $this->insertHeader($sheet, $lastCol, 'Reporte de Estudiantes', self::CYAN);

                $sheet->insertNewRowBefore(8, 1);

                $sheet->setCellValue('A8', 'Periodo: ' . $periodName);

                $sheet->mergeCells("A8:H8");

                $sheet->getStyle("A8")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                        'color' => ['argb' => self::DARK]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $headerRow = 8;
                $lastRow   = $sheet->getHighestRow();

                // Fila de cabecera de columnas
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::WHITE], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::HEADER_BG]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => [
                        'top'    => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF0E7490']],
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0C6A80']],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(22);

                // Filas de datos
                for ($i = $headerRow + 1; $i <= $lastRow; $i++) {
                    $isEven = ($i % 2 === 0);
                    $sheet->getStyle("A{$i}:{$lastCol}{$i}")->applyFromArray([
                        'font'      => ['size' => 9, 'name' => 'Arial', 'color' => ['argb' => self::DARK]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $isEven ? self::ROW_ALT : self::WHITE]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::ROW_BORDER]]],
                    ]);
                    foreach (['A', 'B', 'D', 'F', 'G', 'H'] as $col) {
                        $sheet->getStyle("{$col}{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    $sheet->getRowDimension($i)->setRowHeight(18);
                }

                // Borde exterior tabla
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