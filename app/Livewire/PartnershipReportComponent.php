<?php

namespace App\Livewire;

use App\Exports\PartnershipReportExport;
use App\Livewire\Concerns\HasRoleGuard;
use App\Models\User;
use App\Repositories\PartnerRepository;
use App\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PartnershipReportComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'periode')]
    public string $period = 'bulanan';

    #[Url(as: 'from')]
    public string $from = '';

    #[Url(as: 'to')]
    public string $to = '';

    #[Url(as: 'mitra')]
    public ?int $partnerId = null;

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN, User::ROLE_OWNER, User::ROLE_MITRA]);
        // Mitra hanya bisa melihat laporan miliknya
        if (Auth::user()->isMitra()) {
            $this->partnerId = Auth::user()->partner_id;
        }
    }

    public function updatingPeriod(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function updatingPartnerId(): void
    {
        $this->resetPage();
    }

    public function applyPeriod(string $period): void
    {
        $this->period = $period;
        $this->from = '';
        $this->to = '';
        $this->resetPage();
    }

    private function resolveRange(ReportRepository $repo): array
    {
        $from = $this->from ?: null;
        $to = $this->to ?: null;
        if (! $from && ! $to) {
            [$from, $to] = $repo->buildRange($this->period);
        }

        return [$from, $to];
    }

    private function effectivePartnerId(): ?int
    {
        return Auth::user()->isMitra() ? Auth::user()->partner_id : ($this->partnerId ?: null);
    }

    public function exportPdf(ReportRepository $repo)
    {
        [$from, $to] = $this->resolveRange($repo);
        $partnerId = $this->effectivePartnerId();
        $reports = $repo->partnershipReports($from, $to, $partnerId)->get();
        $summary = $repo->partnershipSummary($from, $to, $partnerId);

        $pdf = Pdf::loadView('exports.partnership-report-pdf', compact('reports', 'summary', 'from', 'to'));

        return response()->streamDownload(fn () => print ($pdf->output()), 'laporan-kerjasama-'.now()->format('Ymd_His').'.pdf');
    }

    public function exportExcel(ReportRepository $repo)
    {
        [$from, $to] = $this->resolveRange($repo);
        $partnerId = $this->effectivePartnerId();

        return Excel::download(new PartnershipReportExport($from, $to, $partnerId), 'laporan-kerjasama-'.now()->format('Ymd_His').'.xlsx');
    }

    #[Layout('layouts.app')]
    #[Title('Laporan Kerjasama')]
    public function render(ReportRepository $repo, PartnerRepository $partners)
    {
        [$from, $to] = $this->resolveRange($repo);
        $partnerId = $this->effectivePartnerId();

        return view('livewire.partnership-report-component', [
            'reports' => $repo->partnershipReports($from, $to, $partnerId)->paginate(15),
            'summary' => $repo->partnershipSummary($from, $to, $partnerId),
            'partners' => $partners->active(),
            'fromResolved' => $from,
            'toResolved' => $to,
        ]);
    }
}
