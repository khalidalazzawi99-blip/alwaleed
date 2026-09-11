<?php

namespace App\Exports;

use App\Services\DailyAccountsService;
use Generator;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyAccountsSheet extends DefaultValueBinder implements Export, FromGenerator, WithCustomStartCell, WithCustomValueBinder, WithDrawings, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly DailyAccountsService $service, private readonly array $report, private readonly string $kind) {}

    public function title(): string
    {
        return ['daily' => 'الحسابات اليومية', 'summary' => 'الملخص', 'people' => 'حسب الأشخاص', 'months' => 'حسب الأشهر'][$this->kind];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return match ($this->kind) {
            'daily' => ['التاريخ', 'اليوم', 'المبلغ', 'الجهة / الشخص', 'الملاحظات', 'رقم الشهر'],
            'summary' => ['المؤشر', 'القيمة'],
            'people' => ['الشخص', 'الإجمالي'],
            'months' => ['الشهر', 'الإجمالي'],
        };
    }

    public function generator(): Generator
    {
        $s = $this->report['summary'];
        if ($this->kind === 'daily') {
            foreach ($this->service->query()->with('party')->orderBy('expense_date')->orderBy('id')->lazy(500) as $row) {
                yield [$row->expense_date->format('Y-m-d'), $row->expense_date->locale('ar')->translatedFormat('l'),
                    (float) $row->amount, $row->party->name, $row->notes ?? '', (int) $row->expense_date->month];
            }
            yield ['الإجمالي', '', (float) $s['total'], '', '', ''];
        } elseif ($this->kind === 'summary') {
            yield ['إجمالي المصروف', (float) $s['total']];
            yield ['عدد الحركات', $s['count']];
            yield ['متوسط المصروف', (float) $s['average']];
            yield ['أعلى شخص صرف', $s['top_person']?->party?->name ?? '—'];
            yield ['مبلغ أعلى شخص', (float) ($s['top_person']?->total ?? 0)];
            yield ['أعلى شهر صرف', $s['top_month']['name'] ?? '—'];
            yield ['مبلغ أعلى شهر', (float) ($s['top_month']['total'] ?? 0)];
            yield ['آخر حركة', $s['last_date'] ?? '—'];
        } elseif ($this->kind === 'people') {
            foreach ($s['people'] as $person) {
                yield [$person->party->name, (float) $person->total];
            }
        } else {
            foreach ($s['months'] as $month) {
                yield [$month['name'].' '.$this->report['filters']['year'], (float) $month['total']];
            }
        }
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        // Explicit text type prevents formulas in notes and party names.
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();
        $column = $this->kind === 'daily' ? 'F' : 'B';
        $sheet->setRightToLeft(true);
        foreach ([1 => 'أضواء سيبار', 2 => 'الحسابات اليومية — '.$this->title(),
            3 => 'الفترة: '.$this->report['period'].' | '.$this->report['currency'],
            4 => 'تاريخ إنشاء التقرير: '.$this->report['generatedAt']->format('Y-m-d H:i')] as $row => $text) {
            $sheet->setCellValueExplicit('A'.$row, $text, DataType::TYPE_STRING);
            $sheet->mergeCells('A'.$row.':'.$column.$row);
        }
        $sheet->freezePane('A7');
        $end = $this->kind === 'daily' ? max(6, $last - 1) : $last;
        $sheet->setAutoFilter('A6:'.$column.$end);
        $sheet->getStyle('A1:'.$column.$last)->getAlignment()->setWrapText(true)->setVertical('center');
        $sheet->getStyle('A1:'.$column.'2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A6:'.$column.'6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '51412D']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDE5DC']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(28);
        $format = '#,##0.## "'.$this->report['currency'].'"';
        if ($this->kind === 'daily') {
            foreach (['A' => 18, 'B' => 18, 'C' => 25, 'D' => 28, 'E' => 55, 'F' => 13] as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
            $sheet->getStyle('C7:C'.$last)->getNumberFormat()->setFormatCode($format);
            $sheet->getStyle('A'.$last.':F'.$last)->getFont()->setBold(true);
        } else {
            $sheet->getColumnDimension('A')->setWidth(45);
            $sheet->getColumnDimension('B')->setWidth(40);
            if ($this->kind === 'summary') {
                foreach ([7, 9, 11, 13] as $row) {
                    $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode($format);
                }
            } elseif ($last >= 7) {
                $sheet->getStyle('B7:B'.$last)->getNumberFormat()->setFormatCode($format);
            }
        }

        return [];
    }

    public function drawings(): array
    {
        if (! $this->report['logoPath']) {
            return [];
        }
        $drawing = new Drawing;
        $drawing->setPath($this->report['logoPath']);
        $drawing->setHeight(30);
        $drawing->setCoordinates($this->kind === 'daily' ? 'F1' : 'B1');

        return [$drawing];
    }
}
