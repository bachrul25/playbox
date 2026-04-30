<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\Category;
use App\Models\FinanceCategory;
use App\Models\Income;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Kasir POS')]
#[Layout('layouts.app')]
class PosComponent extends Component
{
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $categoryId = null;

    /** @var array<int, array{id:int,name:string,price:float,qty:int,subtotal:float,stock:int}> */
    public array $cart = [];

    public string $paymentMethod = 'Cash';

    public float $paidAmount = 0;

    public bool $showCheckout = false;

    public ?array $lastInvoice = null;

    public bool $showReceipt = false;

    public function updatedSearch(): void
    { /* noop, just trigger re-render */
    }

    public function updatedCategoryId(): void
    { /* noop */
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product || $product->status !== 'active') {
            $this->dispatch('toast', type: 'error', message: 'Produk tidak tersedia.');

            return;
        }

        $existing = collect($this->cart)->firstWhere('id', $productId);
        $currentQty = $existing['qty'] ?? 0;
        if ($currentQty + 1 > $product->stock) {
            $this->dispatch('toast', type: 'error', message: 'Stok '.$product->name.' tidak cukup.');

            return;
        }

        if ($existing) {
            foreach ($this->cart as $i => $row) {
                if ($row['id'] === $productId) {
                    $this->cart[$i]['qty']++;
                    $this->cart[$i]['subtotal'] = $this->cart[$i]['qty'] * $this->cart[$i]['price'];
                }
            }
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->selling_price,
                'qty' => 1,
                'subtotal' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
            ];
        }
    }

    public function increment(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }
        $row = $this->cart[$index];
        $product = Product::find($row['id']);
        if (! $product || $product->stock < $row['qty'] + 1) {
            $this->dispatch('toast', type: 'error', message: 'Stok tidak cukup.');

            return;
        }
        $this->cart[$index]['qty']++;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
    }

    public function decrement(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }
        $this->cart[$index]['qty']--;
        if ($this->cart[$index]['qty'] <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);

            return;
        }
        $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
    }

    public function removeItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->paidAmount = 0;
        $this->showCheckout = false;
    }

    public function getTotalProperty(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getChangeProperty(): float
    {
        return max(0, $this->paidAmount - $this->total);
    }

    public function openCheckout(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', type: 'error', message: 'Keranjang masih kosong.');

            return;
        }
        $this->paidAmount = $this->total;
        $this->showCheckout = true;
    }

    public function closeCheckout(): void
    {
        $this->showCheckout = false;
    }

    public function checkout(): void
    {
        $this->validate([
            'cart' => 'required|array|min:1',
            'paymentMethod' => 'required|string',
            'paidAmount' => 'required|numeric|min:0',
        ], [
            'cart.required' => 'Keranjang masih kosong.',
        ]);

        if ($this->paidAmount < $this->total) {
            $this->dispatch('toast', type: 'error', message: 'Uang dibayar kurang dari total.');

            return;
        }

        $invoice = null;
        DB::transaction(function () use (&$invoice) {
            $invoiceNumber = 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Transaction::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'invoice_number' => $invoiceNumber,
                'total' => $this->total,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->change,
                'payment_method' => $this->paymentMethod,
                'transaction_date' => now(),
            ]);

            foreach ($this->cart as $row) {
                $product = Product::lockForUpdate()->find($row['id']);
                if (! $product) {
                    continue;
                }
                if ($product->stock < $row['qty']) {
                    throw new \RuntimeException('Stok '.$product->name.' tidak cukup.');
                }
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $row['qty'],
                    'price' => $row['price'],
                    'subtotal' => $row['subtotal'],
                ]);
                $product->decrement('stock', $row['qty']);
                StockLog::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $row['qty'],
                    'description' => 'Penjualan '.$invoiceNumber,
                    'created_by' => auth()->id(),
                ]);
            }

            // Income & cashflow
            $catPos = FinanceCategory::firstWhere('name', 'Penjualan POS');
            Income::create([
                'source' => 'pos',
                'reference_id' => $transaction->id,
                'category_id' => $catPos?->id,
                'amount' => $transaction->total,
                'description' => 'Penjualan '.$invoiceNumber,
                'date' => today(),
            ]);
            Cashflow::create([
                'type' => 'in',
                'source' => 'pos',
                'reference_id' => $transaction->id,
                'amount' => $transaction->total,
                'description' => 'Penjualan '.$invoiceNumber,
                'date' => today(),
            ]);

            $invoice = $transaction->load('details.product', 'user');
        });

        $this->lastInvoice = [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => (float) $invoice->total,
            'paid' => (float) $invoice->paid_amount,
            'change' => (float) $invoice->change_amount,
            'payment_method' => $invoice->payment_method,
            'cashier' => $invoice->user->name ?? '-',
            'items' => $invoice->details->map(fn ($d) => [
                'name' => $d->product->name ?? 'Produk',
                'qty' => $d->quantity,
                'price' => (float) $d->price,
                'subtotal' => (float) $d->subtotal,
            ])->toArray(),
            'date' => $invoice->transaction_date->format('d/m/Y H:i'),
        ];
        $this->cart = [];
        $this->paidAmount = 0;
        $this->showCheckout = false;
        $this->showReceipt = true;
        $this->dispatch('toast', type: 'success', message: 'Transaksi berhasil disimpan.');
    }

    public function closeReceipt(): void
    {
        $this->showReceipt = false;
        $this->lastInvoice = null;
    }

    public function render()
    {
        $products = Product::query()
            ->where('status', 'active')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->orderBy('name')
            ->take(60)
            ->get();

        return view('livewire.pos-component', [
            'products' => $products,
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
