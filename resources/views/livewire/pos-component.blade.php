@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-pos">POS</span>
            <h4 class="fw-bold mb-0 mt-2">Kasir / Penjualan</h4>
            <small class="text-muted">Pilih produk, masukkan ke keranjang, dan checkout.</small>
        </div>
    </div>

    <div class="row g-3">
        {{-- Product grid --}}
        <div class="col-lg-8">
            <div class="card card-summary p-3 mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari produk..." wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" wire:model.live="categoryId">
                            <option value="">Semua kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                @forelse($products as $p)
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card h-100 card-summary text-center p-3 product-card" style="cursor: pointer;" wire:click="addToCart({{ $p->id }})" wire:loading.attr="disabled">
                        <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:60px;height:60px;border-radius:12px;background:#dcfce7;color:#16a34a;font-size:1.6rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="fw-semibold small">{{ $p->name }}</div>
                        <div class="text-success fw-bold mt-1">{{ F::rupiah($p->selling_price) }}</div>
                        <small class="text-muted">Stok: {{ $p->stock }}</small>
                        @if($p->isLowStock())
                            <span class="badge bg-warning text-dark mt-1">Stok menipis</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-light text-center mb-0">Tidak ada produk ditemukan.</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Cart --}}
        <div class="col-lg-4">
            <div class="card card-summary p-3 sticky-top" style="top: 70px;">
                <h6 class="fw-bold border-bottom pb-2 mb-2"><i class="bi bi-cart3 me-1"></i> Keranjang ({{ count($cart) }})</h6>
                @if(empty($cart))
                    <div class="text-muted text-center py-4 small">Keranjang masih kosong.</div>
                @else
                <div class="cart-items" style="max-height: 320px; overflow-y: auto;">
                    @foreach($cart as $i => $row)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="flex-grow-1">
                            <div class="small fw-semibold">{{ $row['name'] }}</div>
                            <small class="text-muted">{{ F::rupiah($row['price']) }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button class="btn btn-sm btn-outline-secondary px-2" wire:click="decrement({{ $i }})">-</button>
                            <span class="px-1 fw-semibold">{{ $row['qty'] }}</span>
                            <button class="btn btn-sm btn-outline-success px-2" wire:click="increment({{ $i }})">+</button>
                            <button class="btn btn-sm btn-link text-danger ms-1 p-0" wire:click="removeItem({{ $i }})"><i class="bi bi-x-circle"></i></button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <hr>
                <div class="d-flex justify-content-between"><span>Total Item</span><span>{{ collect($cart)->sum('qty') }}</span></div>
                <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-success">{{ F::rupiah($this->total) }}</span></div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-success" wire:click="openCheckout"><i class="bi bi-credit-card me-1"></i> Bayar / Checkout</button>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="clearCart">Kosongkan</button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Checkout modal --}}
    @if($showCheckout)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-cash-coin me-1"></i> Pembayaran</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeCheckout"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="text-muted small">Total Pembayaran</div>
                        <h2 class="fw-bold text-success">{{ F::rupiah($this->total) }}</h2>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Metode Pembayaran</label>
                        <select class="form-select" wire:model="paymentMethod">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->name }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Uang Dibayar</label>
                        <input type="number" step="any" class="form-control form-control-lg" wire:model.live="paidAmount" min="0">
                    </div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach([20000, 50000, 100000, 200000] as $nom)
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('paidAmount', {{ max($nom, $this->total) }})">{{ F::rupiah($nom) }}</button>
                        @endforeach
                        <button type="button" class="btn btn-sm btn-outline-success" wire:click="$set('paidAmount', {{ $this->total }})">Pas</button>
                    </div>
                    <div class="bg-light p-3 rounded text-center">
                        <small class="text-muted">Kembalian</small>
                        <h4 class="fw-bold {{ $this->change > 0 ? 'text-success' : 'text-secondary' }} mb-0">{{ F::rupiah($this->change) }}</h4>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" wire:click="closeCheckout">Batal</button>
                    <button class="btn btn-success" wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout">
                        <span wire:loading.remove wire:target="checkout"><i class="bi bi-check-lg"></i> Konfirmasi</span>
                        <span wire:loading wire:target="checkout">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Receipt modal --}}
    @if($showReceipt && $lastInvoice)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-receipt me-1"></i> Struk Transaksi</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeReceipt"></button>
                </div>
                <div class="modal-body">
                    <div id="receipt-print" class="p-3" style="font-family: monospace;">
                        <div class="text-center">
                            <h6 class="mb-0 fw-bold">SISTEM MANAJEMEN USAHA TERPADU</h6>
                            <small>POS · Rental Playbox</small>
                            <hr class="my-2">
                            <div>{{ $lastInvoice['invoice_number'] }}</div>
                            <small class="text-muted">{{ $lastInvoice['date'] }} · Kasir: {{ $lastInvoice['cashier'] }}</small>
                        </div>
                        <hr class="my-2">
                        @foreach($lastInvoice['items'] as $item)
                            <div class="d-flex justify-content-between small">
                                <span>{{ $item['name'] }}</span>
                                <span>{{ $item['qty'] }} x {{ F::rupiah($item['price']) }}</span>
                            </div>
                            <div class="text-end small">{{ F::rupiah($item['subtotal']) }}</div>
                        @endforeach
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span><span>{{ F::rupiah($lastInvoice['total']) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Bayar ({{ $lastInvoice['payment_method'] }})</span><span>{{ F::rupiah($lastInvoice['paid']) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Kembali</span><span>{{ F::rupiah($lastInvoice['change']) }}</span></div>
                        <hr class="my-2">
                        <div class="text-center small text-muted">Terima kasih atas kunjungannya!</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" wire:click="closeReceipt">Tutup</button>
                    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('styles')
    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .product-card:hover { transform: translateY(-2px); transition: transform .15s; }
    </style>
    @endpush
</div>
