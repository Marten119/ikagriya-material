<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithStyles, WithTitle
{
    private int $headerRow = 1;
    private int $dataEnd = 1;
    private ?int $sumHeaderRow = null;
    private int $sumEnd = 0;
    private array $widths = [];

    public function __construct(private array $report)
    {
    }

    public function title(): string
    {
        return 'Laporan';
    }

    public function array(): array
    {
        $r = $this->report;
        $out = [];

        $out[] = [$r['company']];
        $out[] = [$r['title']];
        $out[] = ['Periode: '.$r['period']];
        $out[] = ['Susunan: '.$r['layout_label']];
        if ($r['filters']) {
            $out[] = ['Filter: '.implode('; ', $r['filters'])];
        }
        $out[] = ['Dibuat: '.$r['generated_at'].' oleh '.$r['generated_by']];
        $out[] = [];

        $this->headerRow = count($out) + 1;
        $out[] = $r['headings'];
        $this->trackWidths($r['headings']);

        foreach ($r['rows'] as $row) {
            $cells = $this->cells($row);
            $this->trackWidths($cells);
            $out[] = $cells;
        }
        $this->dataEnd = count($out);

        if ($r['summary']) {
            $out[] = [];
            $out[] = ['Ringkasan'];
            $this->sumHeaderRow = count($out) + 1;
            $out[] = $r['summary']['headings'];
            $this->trackWidths($r['summary']['headings']);
            foreach ($r['summary']['rows'] as $row) {
                $cells = $this->cells($row);
                $this->trackWidths($cells);
                $out[] = $cells;
            }
            $this->sumEnd = count($out);
        }

        $out[] = [];
        $out[] = ['Total transaksi: '.$r['total_transactions']];
        foreach ($r['notes'] as $note) {
            $out[] = [$note];
        }

        return $out;
    }

    public function styles(Worksheet $sheet): array
    {
        $r = $this->report;

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        foreach ($this->widths as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth(min(max($w + 2, 10), 45));
        }

        $this->styleHeader($sheet, $this->headerRow, count($r['headings']));
        $this->applyFormats($sheet, $r['col_decimals'], $this->headerRow + 1, $this->dataEnd);

        if ($r['summary'] && $this->sumHeaderRow) {
            $sheet->getStyle('A'.($this->sumHeaderRow - 1))->getFont()->setBold(true);
            $this->styleHeader($sheet, $this->sumHeaderRow, count($r['summary']['headings']));
            $this->applyFormats($sheet, $r['summary']['col_decimals'], $this->sumHeaderRow + 1, $this->sumEnd);
        }

        return [];
    }

    private function cells(array $row): array
    {
        return array_map(fn ($c) => is_array($c) ? (float) $c[0] : $c, $row);
    }

    private function trackWidths(array $cells): void
    {
        foreach (array_values($cells) as $i => $c) {
            $len = mb_strlen((string) $c);
            $this->widths[$i] = max($this->widths[$i] ?? 0, $len);
        }
    }

    private function styleHeader(Worksheet $sheet, int $row, int $cols): void
    {
        $range = 'A'.$row.':'.Coordinate::stringFromColumnIndex($cols).$row;
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true);
        $style->getFont()->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID);
        $style->getFill()->getStartColor()->setRGB('1F3A5F');
    }

    private function applyFormats(Worksheet $sheet, array $decimals, int $start, int $end): void
    {
        if ($end < $start) {
            return;
        }

        foreach ($decimals as $i => $d) {
            if ($d === null) {
                continue;
            }
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $code = $d === 0 ? '#,##0' : '#,##0.'.str_repeat('0', $d);
            $sheet->getStyle("{$col}{$start}:{$col}{$end}")->getNumberFormat()->setFormatCode($code);
        }
    }
}
