@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Produk</h4>
            <small class="text-muted">Kelola produk untuk modul kasir.</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</button>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" class="form-control" placeholder="Cari produk..." wire:model.live.debounce.300ms="search">
            </div>
            <div class="col-md-4">
                <select class="form-select" wire:model.live="filterCategory">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterStatus">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga Modal</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($p->image)
                                    <img src="{{ asset('storage/'.$p->image) }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:8px;background:#dcfce7;color:#16a34a;"><i class="bi bi-box-seam"></i></div>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $p->name }}</div>
                                    @if($p->isLowStock())<span class="badge bg-warning text-dark">Stok menipis</span>@endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->category->name ?? '-' }}</td>
                        <td>{{ F::rupiah($p->purchase_price) }}</td>
                        <td class="fw-semibold text-success">{{ F::rupiah($p->selling_price) }}</td>
                        <td>{{ $p->stock }} <small class="text-muted">/min {{ $p->minimum_stock }}</small></td>
                        <td><span class="badge bg-{{ $p->status === 'active' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $p->id }})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteProduct" data-id="{{ $p->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $products->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit Produk' : 'Tambah Produk' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" wire:model.defer="category_id">
                                    <option value="">- pilih -</option>
                                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Modal</label>
                                <input type="number" step="any" min="0" class="form-control @error('purchase_price') is-invalid @enderror" wire:model.defer="purchase_price">
                                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual</label>
                                <input type="number" step="any" min="0" class="form-control @error('selling_price') is-invalid @enderror" wire:model.defer="selling_price">
                                @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stok</label>
                                <input type="number" min="0" class="form-control" wire:model.defer="stock">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stok Minimum</label>
                                <input type="number" min="0" class="form-control" wire:model.defer="minimum_stock">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" wire:model.defer="status">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Gambar Produk</label>
                                <input type="file" class="form-control" wire:model="imageFile" accept="image/*">
                                @error('imageFile')<div class="text-danger small">{{ $message }}</div>@enderror
                                @if($imageFile)
                                    <img src="{{ $imageFile->temporaryUrl() }}" class="mt-2 rounded" style="max-height:100px;">
                                @elseif($existingImage)
                                    <img src="{{ asset('storage/'.$existingImage) }}" class="mt-2 rounded" style="max-height:100px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeModal">Batal</button>
                        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
