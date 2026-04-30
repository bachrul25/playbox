@php use App\Helpers\FormatHelper as F; @endphp
<div wire:poll.30s>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-rental">Rental</span>
            <h4 class="fw-bold mb-0 mt-2">Rental Playbox</h4>
            <small class="text-muted">Pilih unit untuk memulai sesi atau kelola sesi aktif.</small>
        </div>
    </div>

    <h6 class="fw-bold mb-2"><i class="bi bi-tv me-1"></i> Daftar Unit</h6>
    <div class="row g-3 mb-4">
        @foreach($units as $u)
        @php
            $statusColors = [
                'available' => ['bg' => 'rgba(22,163,74,.1)', 'border' => '#16a34a', 'badge' => 'success', 'label' => 'Tersedia'],
                'in_use' => ['bg' => 'rgba(37,99,235,.1)', 'border' => '#2563eb', 'badge' => 'primary', 'label' => 'Disewa'],
                'maintenance' => ['bg' => 'rgba(245,158,11,.1)', 'border' => '#f59e0b', 'badge' => 'warning text-dark', 'label' => 'Maintenance'],
                'inactive' => ['bg' => 'rgba(107,114,128,.1)', 'border' => '#6b7280', 'badge' => 'secondary', 'label' => 'Nonaktif'],
            ];
            $sc = $statusColors[$u->status];
        @endphp
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100" style="border: 2px solid {{ $sc['border'] }}; background: {{ $sc['bg'] }};">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $u->code }}</strong>
                            <div class="small text-muted">{{ $u->location ?? 'Lokasi -' }}</div>
                        </div>
                        <span class="badge bg-{{ $sc['badge'] }}">{{ $sc['label'] }}</span>
                    </div>
                    <h5 class="fw-bold mt-2 mb-0"><i class="bi bi-controller me-1"></i> {{ $u->name }}</h5>
                    <small class="text-muted">{{ $u->type }} · {{ F::rupiah($u->hourly_price) }}/jam</small>

                    @if($u->status === 'available')
                        <button class="btn btn-success w-100 mt-3" wire:click="openStart({{ $u->id }})"><i class="bi bi-play-fill"></i> Mulai Sesi</button>
                    @elseif($u->status === 'in_use' && $u->activeRental)
                        <div class="bg-white rounded p-2 mt-3 small">
                            <div><strong>{{ $u->activeRental->customer_name }}</strong></div>
                            <div class="text-muted">Mulai: {{ $u->activeRental->start_time->format('H:i') }}</div>
                            <div class="text-primary fw-bold timer-display" data-start="{{ $u->activeRental->start_time->timestamp }}">--</div>
                        </div>
                        <div class="d-grid gap-1 mt-2">
                            <button class="btn btn-warning btn-sm" wire:click="openExtend({{ $u->activeRental->id }})"><i class="bi bi-plus-lg"></i> Perpanjang</button>
                            <button class="btn btn-danger btn-sm" wire:click="openFinish({{ $u->activeRental->id }})"><i class="bi bi-stop-fill"></i> Selesai</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Start modal --}}
    @if($showStartModal && $startUnit)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="startRental">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Mulai Sesi - {{ $startUnit->name }} ({{ $startUnit->code }})</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showStartModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" wire:model.defer="customer_name">
                            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mode</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" wire:model.live="mode" value="open" id="mode-open">
                                <label class="btn btn-outline-primary" for="mode-open">Bebas waktu</label>
                                <input type="radio" class="btn-check" wire:model.live="mode" value="fixed" id="mode-fixed">
                                <label class="btn btn-outline-primary" for="mode-fixed">Durasi tetap</label>
                            </div>
                        </div>
                        @if($mode === 'fixed')
                        <div class="mb-3">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" min="1" class="form-control" wire:model.defer="planned_minutes">
                        </div>
                        @endif
                        <div class="mb-1">
                            <label class="form-label">Metode Pembayaran (saat selesai)</label>
                            <select class="form-select" wire:model.defer="payment_method">
                                @foreach($paymentMethods as $pm)<option>{{ $pm->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="bg-light p-2 rounded mt-3 small text-muted">
                            Tarif: <strong>{{ F::rupiah($startUnit->hourly_price) }}/jam</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showStartModal', false)">Batal</button>
                        <button class="btn btn-primary"><i class="bi bi-play-fill me-1"></i> Mulai Sesi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Extend modal --}}
    @if($showExtendModal && $activeRental)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="extend">
                    <div class="modal-header bg-warning"><h5 class="modal-title">Perpanjang Waktu - {{ $activeRental->customer_name }}</h5><button type="button" class="btn-close" wire:click="$set('showExtendModal', false)"></button></div>
                    <div class="modal-body">
                        <label class="form-label">Tambah Waktu (menit)</label>
                        <input type="number" min="1" class="form-control" wire:model.defer="extendMinutes">
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @foreach([15,30,60,120] as $m)
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('extendMinutes', {{ $m }})">+{{ $m }} mnt</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showExtendModal', false)">Batal</button>
                        <button class="btn btn-warning text-dark"><i class="bi bi-plus-lg"></i> Perpanjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Finish modal --}}
    @if($showFinishModal && $activeRental)
    @php $calc = $this->calculated; @endphp
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="finish">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-stop-fill me-1"></i> Selesai - {{ $activeRental->unit->name }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showFinishModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row text-center mb-3">
                            <div class="col"><small class="text-muted">Pelanggan</small><div class="fw-semibold">{{ $activeRental->customer_name }}</div></div>
                            <div class="col"><small class="text-muted">Mulai</small><div class="fw-semibold">{{ $activeRental->start_time->format('H:i') }}</div></div>
                            <div class="col"><small class="text-muted">Durasi</small><div class="fw-semibold">{{ \App\Helpers\FormatHelper::durationHuman($calc['minutes']) }}</div></div>
                        </div>
                        <div class="bg-light p-3 rounded text-center mb-3">
                            <small class="text-muted">Total Biaya</small>
                            <h3 class="fw-bold text-success mb-0">{{ F::rupiah($calc['total']) }}</h3>
                            <small class="text-muted">{{ F::rupiah($activeRental->hourly_price) }}/jam · {{ $calc['minutes'] }} menit</small>
                        </div>
                        <label class="form-label">Metode Pembayaran</label>
                        <select class="form-select" wire:model.defer="payment_method">
                            @foreach($paymentMethods as $pm)<option>{{ $pm->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showFinishModal', false)">Batal</button>
                        <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Bayar & Selesai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Receipt modal --}}
    @if($showReceipt && $lastReceipt)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white"><h5 class="modal-title">Nota Rental</h5><button type="button" class="btn-close btn-close-white" wire:click="closeReceipt"></button></div>
                <div class="modal-body">
                    <div id="receipt-rental" style="font-family: monospace;">
                        <div class="text-center"><h6 class="fw-bold">RENTAL PLAYBOX</h6><small>SMU Terpadu</small></div>
                        <hr>
                        <div>Invoice: <strong>{{ $lastReceipt['invoice'] }}</strong></div>
                        <div>Pelanggan: {{ $lastReceipt['customer'] }}</div>
                        <div>Unit: {{ $lastReceipt['unit'] }}</div>
                        <div>Mulai: {{ $lastReceipt['start'] }}</div>
                        <div>Selesai: {{ $lastReceipt['end'] }}</div>
                        <div>Durasi: {{ \App\Helpers\FormatHelper::durationHuman($lastReceipt['minutes']) }}</div>
                        <div>Tarif: {{ F::rupiah($lastReceipt['hourly']) }}/jam</div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span><span>{{ F::rupiah($lastReceipt['total']) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Bayar</span><span>{{ $lastReceipt['method'] }}</span></div>
                        <hr>
                        <div class="text-center small text-muted">Terima kasih, sampai jumpa lagi!</div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" wire:click="closeReceipt">Tutup</button><button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak</button></div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        function refreshTimers() {
            document.querySelectorAll('.timer-display').forEach(el => {
                const start = parseInt(el.dataset.start, 10) * 1000;
                const diff = Date.now() - start;
                const sec = Math.floor(diff / 1000);
                const h = Math.floor(sec / 3600);
                const m = Math.floor((sec % 3600) / 60);
                const s = sec % 60;
                el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            });
        }
        setInterval(refreshTimers, 1000);
        refreshTimers();
    </script>
    @endpush
</div>
