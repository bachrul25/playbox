<?php

namespace App\Livewire;

use App\Exports\PrivateReportExport;
use App\Livewire\Concerns\HasRoleGuard;
use App\Models\User;
use App\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PrivateReportComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'periode')]
    public string $period = 'bulanan';

    #[Url(as: 'from')]
    public string $from = '';

    #[Url(as: 'to')]
    public string $to = '';

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN, User::ROLE_OWNER]);
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

    public function exportPdf(ReportRepository $repo)
    {
        [$from, $to] = $this->resolveRange($repo);
        $reports = $repo->privateReports($from, $to)->get();
        $summary = $repo->privateSummary($from, $to);

        $pdf = Pdf::loadView('exports.private-report-pdf', compact('reports', 'summary', 'from', 'to'));

        return response()->streamDownload(fn () => print ($pdf->output()), 'laporan-pribadi-'.now()->format('Ymd_His').'.pdf');
    }

    public function exportExcel(ReportRepository $repo)
    {
        [$from, $to] = $this->resolveRange($repo);

        return Excel::download(new PrivateReportExport($from, $to), 'laporan-pribadi-'.now()->format('Ymd_His').'.xlsx');
    }

    #[Layout('layouts.app')]
    #[Title('Laporan Pribadi')]
    public function render(ReportRepository $repo)
    {
        [$from, $to] = $this->resolveRange($repo);

        return view('livewire.private-report-component', [
            'reports' => $repo->privateReports($from, $to)->paginate(15),
            'summary' => $repo->privateSummary($from, $to),
            'fromResolved' => $from,
            'toResolved' => $to,
        ]);
    }
}
