<?php

namespace App\Livewire;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Title('Riwayat Transaksi POS')]
#[Layout('layouts.app')]
class TransactionHistoryComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $startDate = '';

    #[Url(history: true)]
    public string $endDate = '';

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
        $pdf = Pdf::loadView('exports.transactions-pdf', [
            'rows' => $rows,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'transaksi-'.now()->format('Ymd-His').'.pdf',
        );
    }

    public function exportExcel(): StreamedResponse
    {
        $rows = $this->buildQuery()->get();
        $filename = 'transaksi-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice', 'Tanggal', 'Kasir', 'Total', 'Bayar', 'Kembali', 'Metode']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->invoice_number,
                    optional($r->transaction_date)->format('Y-m-d H:i'),
                    $r->user->name ?? '-',
                    $r->total, $r->paid_amount, $r->change_amount, $r->payment_method,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildQuery()
    {
        return Transaction::with('user', 'details.product')
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ->when($this->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $this->endDate))
            ->orderByDesc('transaction_date');
    }

    public function render()
    {
        return view('livewire.transaction-history-component', [
            'transactions' => $this->buildQuery()->paginate(10),
            'detail' => $this->detailId ? Transaction::with('user', 'details.product')->find($this->detailId) : null,
        ]);
    }
}
