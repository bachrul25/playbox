<?php

namespace App\Exports;

use App\Models\PartnershipReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartnershipReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private ?string $from = null, private ?string $to = null, private ?int $partnerId = null) {}

    public function collection()
    {
        return PartnershipReport::query()
            ->with(['rental.playbox', 'partner'])
            ->when($this->from, fn ($q) => $q->whereDate('report_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('report_date', '<=', $this->to))
            ->when($this->partnerId, fn ($q) => $q->where('partner_id', $this->partnerId))
            ->orderByDesc('report_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Invoice',
            'Mitra/Cafe',
            'PlayBox',
            'Total Pendapatan',
            'Biaya Staff',
            'Sisa Bersih',
            'Bagian Owner (50%)',
            'Bagian Cafe (50%)',
        ];
    }

    public function map($row): array
    {
        return [
            optional($row->report_date)->format('d-m-Y'),
            optional($row->rental)->invoice_number,
            optional($row->partner)->cafe_name,
            optional(optional($row->rental)->playbox)->name,
            (float) $row->total_income,
            (float) $row->staff_cost,
            (float) $row->net_income,
            (float) $row->owner_share,
            (float) $row->partner_share,
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
        return 'Laporan Kerjasama';
    }
}
