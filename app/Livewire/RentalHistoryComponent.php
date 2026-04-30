<?php

namespace App\Livewire;

use App\Models\Rental;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Riwayat Rental')]
#[Layout('layouts.app')]
class RentalHistoryComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $startDate = '';

    #[Url(history: true)]
    public string $endDate = '';

    #[Url(history: true)]
    public string $filterStatus = '';

    public ?int $detailId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function viewDetail(int $id): void
    {
        $this->detailId = $id;
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    public function exportPdf()
    {
        $rows = $this->buildQuery()->get();
        $pdf = Pdf::loadView('exports.rentals-pdf', [
            'rows' => $rows,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'rental-'.now()->format('Ymd-His').'.pdf',
        );
    }

    public function exportExcel()
    {
        $rows = $this->buildQuery()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice', 'Pelanggan', 'Unit', 'Mulai', 'Selesai', 'Durasi (mnt)', 'Tarif/Jam', 'Total', 'Status']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->invoice_number, $r->customer_name, $r->unit->name ?? '-',
                    optional($r->start_time)->format('Y-m-d H:i'),
                    optional($r->end_time)->format('Y-m-d H:i'),
                    $r->duration_minutes, $r->hourly_price, $r->total_price, $r->status,
                ]);
            }
            fclose($out);
        }, 'rental-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildQuery()
    {
        return Rental::with('unit', 'user')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('invoice_number', 'like', "%{$this->search}%")->orWhere('customer_name', 'like', "%{$this->search}%")))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->startDate, fn ($q) => $q->whereDate('start_time', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('start_time', '<=', $this->endDate))
            ->orderByDesc('start_time');
    }

    public function render()
    {
        return view('livewire.rental-history-component', [
            'rentals' => $this->buildQuery()->paginate(10),
            'detail' => $this->detailId ? Rental::with('unit', 'user', 'sessions')->find($this->detailId) : null,
        ]);
    }
}
