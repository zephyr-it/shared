<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Exports\Sheets;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseSheet implements FromCollection, ShouldAutoSize, ShouldQueue, WithColumnFormatting, WithEvents, WithHeadings, WithStyles, WithTitle
{
    protected string $title;

    protected Collection $data;

    protected array $headings = [];

    protected array $rowStyles = [];

    protected ?string $notesRow = null;

    // Legend
    protected bool $hasLegend = false;

    protected array $legendRows = [];

    protected array $legendStyles = [];

    protected int $legendRowCount = 0;

    // Configurable options
    protected ?string $headerColor = '1E88E5';

    protected bool $applyBorders = true;

    protected bool $freezeHeader = true;

    protected bool $useAutoFilter = true;

    protected array $columnWidths = [];

    protected array $mergeCells = [];

    protected bool $enableLogging = false;

    public function __construct(
        string $title,
        Collection $data,
        array $headings = [],
        ?string $headerColor = '1E88E5',
        array $legendRows = [],
        array $legendStyles = [],
        array $columnWidths = [],
        array $mergeCells = [],
        array $rowStyles = [],
        bool $applyBorders = true,
        bool $freezeHeader = true,
        bool $useAutoFilter = true,
        bool $enableLogging = false,
        ?string $notesRow = null,
    ) {
        $this->title = $title;
        $this->data = $data;
        $this->headings = $headings;
        $this->headerColor = $headerColor;
        $this->legendRows = $legendRows;
        $this->legendStyles = $legendStyles;
        $this->rowStyles = $rowStyles;
        $this->columnWidths = $columnWidths;
        $this->mergeCells = $mergeCells;
        $this->enableLogging = $enableLogging;
        $this->applyBorders = $applyBorders;
        $this->freezeHeader = $freezeHeader;
        $this->useAutoFilter = $useAutoFilter;
        $this->notesRow = $notesRow;

        if (! empty($legendRows)) {
            $this->hasLegend = true;
            $this->legendRowCount = count($legendRows) + 1;
        }
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        return $this->rowStyles;
    }

    public function columnFormats(): array
    {
        return []; // Override in subclasses
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings));
                $currentRow = 1;

                // 📝 Insert Notes Row
                if ($this->notesRow) {
                    $sheet->insertNewRowBefore($currentRow, 1);
                    $mergeToColIndex = min(2, count($this->headings));
                    $mergeEndCol = Coordinate::stringFromColumnIndex($mergeToColIndex);

                    $sheet->mergeCells("A{$currentRow}:{$mergeEndCol}{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", $this->notesRow);

                    $sheet->getStyle("A{$currentRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                    ]);

                    // ✅ Fixed height (or use -1 for auto)
                    $sheet->getRowDimension($currentRow)->setRowHeight(45);

                    $currentRow++;
                }

                // 🟩 Insert Legend Rows
                if ($this->hasLegend) {
                    $legendStartRow = $currentRow;

                    $sheet->insertNewRowBefore($legendStartRow, $this->legendRowCount);

                    // 🧼 Clear inherited height
                    for ($i = 0; $i < $this->legendRowCount; $i++) {
                        $sheet->getRowDimension($legendStartRow + $i)->setRowHeight(-1);
                    }

                    // 🏷️ Legend Title
                    $sheet->setCellValue("A{$legendStartRow}", 'Legend:');
                    $sheet->getStyle("A{$legendStartRow}")->getFont()->setBold(true);

                    $row = $legendStartRow + 1;

                    foreach ($this->legendRows as $legendRowIdx => $legendRow) {
                        foreach ($legendRow as $colIdx => $val) {
                            $cell = Coordinate::stringFromColumnIndex($colIdx + 1) . $row;
                            $sheet->setCellValue($cell, $val);

                            if (isset($this->legendStyles[$legendRowIdx][$colIdx])) {
                                $sheet->getStyle($cell)->applyFromArray($this->legendStyles[$legendRowIdx][$colIdx]);
                            }
                        }

                        // ✅ Ensure each legend row height is reset
                        $sheet->getRowDimension($row)->setRowHeight(-1);
                        $row++;
                    }

                    $currentRow += $this->legendRowCount;
                }

                // 🎯 Header
                $headerRow = $currentRow;
                $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $this->headerColor],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                if ($this->useAutoFilter) {
                    $sheet->setAutoFilter($headerRange);
                }

                // 🧷 Freeze Header
                if ($this->freezeHeader) {
                    $sheet->freezePane('A' . ($headerRow + 1));
                }

                // 📏 Column Widths
                foreach ($this->columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // 🔄 Merge extra cells
                foreach ($this->mergeCells as $range) {
                    $sheet->mergeCells($range);
                }

                // 🧱 Apply Borders
                if ($this->applyBorders && ! empty($this->headings)) {
                    $lastRow = $sheet->getHighestRow();
                    $borderRange = "A{$headerRow}:{$lastCol}{$lastRow}";

                    $sheet->getStyle($borderRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                }

                // 🪵 Logging
                if ($this->enableLogging) {
                    logger()->debug('[Excel Export]', [
                        'sheet' => $this->title,
                        'rows' => $this->data->count(),
                        'columns' => count($this->headings),
                        'hasNotes' => $this->notesRow !== null,
                        'hasLegend' => $this->hasLegend,
                    ]);
                }
            },
        ];
    }
}
