<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Generic Excel/CSV export for any {headings, rows} report shape produced
 * by ReportService — one export class serves every report type.
 */
class ReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        private readonly array $headings,
        private readonly array $rows,
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function collection(): Collection
    {
        return collect($this->rows);
    }
}
