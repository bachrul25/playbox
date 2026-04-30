<?php

namespace Database\Seeders;

use App\Models\Cashflow;
use App\Models\Category;
use App\Models\Expense;
use App\Models\FinanceCategory;
use App\Models\Income;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Users ----------
        $users = [
            ['name' => 'Admin Utama', 'email' => 'admin@gmail.com', 'role' => 'admin'],
            ['name' => 'Owner Usaha', 'email' => 'owner@gmail.com', 'role' => 'owner'],
            ['name' => 'Kasir 1',     'email' => 'kasir@gmail.com', 'role' => 'kasir'],
            ['name' => 'Operator 1',  'email' => 'operator@gmail.com', 'role' => 'operator'],
        ];
        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['password' => Hash::make('password'), 'status' => 'active']),
            );
        }

        // ---------- Payment methods ----------
        foreach (['Cash', 'Transfer', 'QRIS', 'E-wallet', 'Lainnya'] as $pm) {
            PaymentMethod::firstOrCreate(['name' => $pm], ['status' => 'active']);
        }

        // ---------- Product categories ----------
        $cats = [
            ['name' => 'Makanan',  'description' => 'Produk makanan ringan & berat'],
            ['name' => 'Minuman',  'description' => 'Aneka minuman dingin & panas'],
            ['name' => 'Snack',    'description' => 'Camilan & makanan ringan'],
            ['name' => 'Aksesoris', 'description' => 'Kontroler, headset, dll.'],
        ];
        foreach ($cats as $c) {
            Category::firstOrCreate(['name' => $c['name']], array_merge($c, ['status' => 'active']));
        }

        // ---------- Products ----------
        $products = [
            ['Nasi Goreng',     'Makanan', 12000, 18000, 25, 5],
            ['Mie Goreng',      'Makanan', 10000, 15000, 30, 5],
            ['Kentang Goreng',  'Snack',    8000, 14000, 20, 5],
            ['Roti Bakar',      'Snack',    7000, 12000, 18, 5],
            ['Es Teh Manis',    'Minuman',  3000,  6000, 50, 10],
            ['Kopi Hitam',      'Minuman',  4000,  8000, 40, 10],
            ['Air Mineral',     'Minuman',  2000,  4000, 60, 10],
            ['Indomie Rebus',   'Makanan',  4000,  9000, 35, 8],
            ['Permen Mint',     'Snack',    1000,  2000, 100, 20],
            ['Sticker PS',      'Aksesoris', 5000, 10000, 15, 3],
        ];
        foreach ($products as [$name, $catName, $purchase, $sell, $stock, $minStock]) {
            $cat = Category::where('name', $catName)->first();
            Product::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $cat?->id,
                    'purchase_price' => $purchase,
                    'selling_price' => $sell,
                    'stock' => $stock,
                    'minimum_stock' => $minStock,
                    'status' => 'active',
                ],
            );
        }

        // ---------- Rental units ----------
        $units = [
            ['code' => 'PB-01', 'name' => 'Playbox 01', 'type' => 'PS4', 'hourly_price' => 8000,  'location' => 'Meja 1'],
            ['code' => 'PB-02', 'name' => 'Playbox 02', 'type' => 'PS4', 'hourly_price' => 8000,  'location' => 'Meja 2'],
            ['code' => 'PB-03', 'name' => 'Playbox 03', 'type' => 'PS5', 'hourly_price' => 12000, 'location' => 'Meja 3'],
            ['code' => 'PB-04', 'name' => 'Playbox 04', 'type' => 'PS5', 'hourly_price' => 12000, 'location' => 'Meja 4'],
            ['code' => 'PB-05', 'name' => 'Playbox 05', 'type' => 'PS3', 'hourly_price' => 5000,  'location' => 'Meja 5'],
            ['code' => 'PB-06', 'name' => 'Playbox 06', 'type' => 'PS3', 'hourly_price' => 5000,  'location' => 'Meja 6'],
        ];
        foreach ($units as $u) {
            RentalUnit::firstOrCreate(
                ['code' => $u['code']],
                array_merge($u, ['status' => 'available', 'description' => 'Unit rental Playbox']),
            );
        }

        // ---------- Finance categories ----------
        $financeCats = [
            // income
            ['name' => 'Penjualan POS', 'type' => 'income'],
            ['name' => 'Rental Playbox', 'type' => 'income'],
            ['name' => 'Pemasukan Lain', 'type' => 'income'],
            // expense
            ['name' => 'Listrik & Air', 'type' => 'expense'],
            ['name' => 'Gaji Karyawan', 'type' => 'expense'],
            ['name' => 'Pembelian Stok', 'type' => 'expense'],
            ['name' => 'Maintenance',    'type' => 'expense'],
            ['name' => 'Sewa Tempat',    'type' => 'expense'],
            ['name' => 'Operasional',    'type' => 'expense'],
        ];
        foreach ($financeCats as $fc) {
            FinanceCategory::firstOrCreate(
                ['name' => $fc['name']],
                array_merge($fc, ['status' => 'active', 'description' => null]),
            );
        }

        // ---------- Sample manual finance entries (so dashboard isn't empty) ----------
        if (Income::count() === 0) {
            $catLain = FinanceCategory::where('name', 'Pemasukan Lain')->first();
            Income::create([
                'source' => 'manual',
                'category_id' => $catLain?->id,
                'amount' => 250000,
                'description' => 'Modal awal kas',
                'date' => now()->toDateString(),
            ]);
            Cashflow::create([
                'type' => 'in', 'source' => 'manual_income',
                'amount' => 250000, 'description' => 'Modal awal kas',
                'date' => now()->toDateString(),
            ]);
        }

        if (Expense::count() === 0) {
            $catListrik = FinanceCategory::where('name', 'Listrik & Air')->first();
            Expense::create([
                'category_id' => $catListrik?->id,
                'amount' => 150000,
                'description' => 'Tagihan listrik bulan ini',
                'date' => now()->toDateString(),
            ]);
            Cashflow::create([
                'type' => 'out', 'source' => 'expense',
                'amount' => 150000, 'description' => 'Tagihan listrik bulan ini',
                'date' => now()->toDateString(),
            ]);
        }
    }
}
