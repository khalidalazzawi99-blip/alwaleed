<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ArrayExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings
{
    public function __construct(
        private readonly array $headings,
        private readonly array $rows,
        private readonly string $title = 'تقرير مالي',
        private readonly ?int $companyId = null,
    ) {
    }

    public function headings(): array
    {
        return array_map($this->normalize(...), $this->headings);
    }

    public function array(): array
    {
        return array_map(fn (array $row) => array_map($this->normalize(...), $row), $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $companyId = $this->companyId ?: auth()->user()?->company_id;
        $setting = \App\Models\Setting::where('company_id', $companyId)->first();
        $company = \App\Models\Company::find($companyId);
        $companyName = $setting?->company_name ?: ($company?->name ?: 'Al Waleed');

        $sheet->setRightToLeft(true);
        $sheet->setCellValue('B1', $this->title);
        $sheet->setCellValue('B2', $companyName.'  |  '.($setting?->phone ?: '-').'  |  '.($setting?->email ?: '-'));
        $sheet->mergeCells('B1:'.$lastColumn.'1');
        $sheet->mergeCells('B2:'.$lastColumn.'2');
        $sheet->getRowDimension(1)->setRowHeight(42);
        $sheet->getRowDimension(2)->setRowHeight(23);
        $sheet->getRowDimension(3)->setRowHeight(6);
        $sheet->freezePane('A6');
        $sheet->setAutoFilter('A5:'.$lastColumn.$sheet->getHighestRow());
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('B1:'.$lastColumn.'1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '192944']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('B2:'.$lastColumn.'2')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '7D8797']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A3:'.$lastColumn.'3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8A878']],
        ]);
        $sheet->getStyle('A5:'.$lastColumn.'5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '192944']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A6:'.$lastColumn.$sheet->getHighestRow())->applyFromArray([
            'borders' => ['bottom' => ['borderStyle' => 'hair', 'color' => ['rgb' => 'DFE4EB']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function drawings(): array
    {
        $companyId = $this->companyId ?: auth()->user()?->company_id;
        $relativePath = \App\Models\Setting::where('company_id', $companyId)->value('company_logo')
            ?: \App\Models\Company::whereKey($companyId)->value('logo');
        $path = $relativePath ? \Illuminate\Support\Facades\Storage::disk('public')->path($relativePath) : public_path('logo.png');

        if (! is_file($path)) {
            return [];
        }

        $logo = new Drawing();
        $logo->setName($this->title);
        $logo->setPath($path);
        $logo->setHeight(38);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(8);
        $logo->setOffsetY(4);

        return [$logo];
    }

    private function normalize(mixed $value): string|int|float
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $value = (string) ($value ?? '');

        // Prevent spreadsheet programs from interpreting user text as formulas.
        return preg_match('/^[=+\-@]/u', $value) ? "'".$value : $value;
    }
}
