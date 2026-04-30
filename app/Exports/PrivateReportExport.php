<?php

namespace App\Exports;

use App\Models\PrivateReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PrivateReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private ?string $from = null, private ?string $to = null) {}

    public function collection()
    {
        return PrivateReport::query()
            ->with('rental.playbox')
            ->when($this->from, fn ($q) => $q->whereDate('report_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('report_date', '<=', $this->to))
            ->orderByDesc('report_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Invoice',
            'PlayBox',
            'Total Pendapatan',
            'Maintenance (20%)',
            'Keuntungan Owner (80%)',
        ];
    }

    public function map($row): array
    {
        return [
            optional($row->report_date)->format('d-m-Y'),
            optional($row->rental)->invoice_number,
            optional(optional($row->rental)->playbox)->name,
            (float) $row->total_income,
            (float) $row->maintenance_amount,
            (float) $row->owner_profit,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Pribadi';
    }
}
