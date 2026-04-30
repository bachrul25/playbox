<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Partner;
use App\Models\Playbox;
use App\Models\Rental;
use App\Models\User;
use App\Repositories\RentalRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Partners
        $tata = Partner::firstOrCreate(
            ['cafe_name' => 'Cafe Tata Krama'],
            [
                'person_in_charge' => 'Bu Tata',
                'phone' => '081234567001',
                'address' => 'Jl. Merdeka No. 10, Surabaya',
                'cooperation_start_date' => now()->subMonths(6)->toDateString(),
                'status' => 'aktif',
                'note' => 'Kerjasama bagi hasil 50:50.',
            ],
        );

        $bahagia = Partner::firstOrCreate(
            ['cafe_name' => 'Cafe Bahagia'],
            [
                'person_in_charge' => 'Pak Hadi',
                'phone' => '081234567002',
                'address' => 'Jl. Pemuda No. 25, Surabaya',
                'cooperation_start_date' => now()->subMonths(3)->toDateString(),
                'status' => 'aktif',
                'note' => 'Buka 7 hari, full operasional.',
            ],
        );

        // 2) Users
        User::firstOrCreate(
            ['email' => 'admin@playbox.com'],
            [
                'name' => 'Admin PlayBox',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
        );

        User::firstOrCreate(
            ['email' => 'owner@playbox.com'],
            [
                'name' => 'Owner PlayBox',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OWNER,
                'is_active' => true,
            ],
        );

        User::firstOrCreate(
            ['email' => 'mitra@playbox.com'],
            [
                'name' => 'Mitra Tata Krama',
                'password' => Hash::make('password'),
                'role' => User::ROLE_MITRA,
                'partner_id' => $tata->id,
                'is_active' => true,
            ],
        );

        $admin = User::where('email', 'admin@playbox.com')->first();

        // 3) Playboxes
        $pbx1 = Playbox::firstOrCreate(
            ['code' => 'PBX001'],
            [
                'name' => 'PlayBox Pribadi 1',
                'ownership_type' => Playbox::OWNERSHIP_PRIBADI,
                'partner_id' => null,
                'location' => 'Gudang Owner - Surabaya',
                'status' => Playbox::STATUS_TERSEDIA,
                'default_price_per_hour' => 50000,
                'condition_note' => 'Unit baru, lengkap controller PS5.',
            ],
        );

        $pbx2 = Playbox::firstOrCreate(
            ['code' => 'PBX002'],
            [
                'name' => 'PlayBox Pribadi 2',
                'ownership_type' => Playbox::OWNERSHIP_PRIBADI,
                'partner_id' => null,
                'location' => 'Gudang Owner - Surabaya',
                'status' => Playbox::STATUS_TERSEDIA,
                'default_price_per_hour' => 45000,
                'condition_note' => 'Unit kedua, kondisi baik.',
            ],
        );

        $pbx3 = Playbox::firstOrCreate(
            ['code' => 'PBX003'],
            [
                'name' => 'PlayBox Cafe Tata Krama',
                'ownership_type' => Playbox::OWNERSHIP_KERJASAMA,
                'partner_id' => $tata->id,
                'location' => 'Cafe Tata Krama',
                'status' => Playbox::STATUS_TERSEDIA,
                'default_price_per_hour' => 60000,
                'condition_note' => 'Stand tetap di Cafe Tata Krama.',
            ],
        );

        $pbx4 = Playbox::firstOrCreate(
            ['code' => 'PBX004'],
            [
                'name' => 'PlayBox Cafe Bahagia',
                'ownership_type' => Playbox::OWNERSHIP_KERJASAMA,
                'partner_id' => $bahagia->id,
                'location' => 'Cafe Bahagia',
                'status' => Playbox::STATUS_TERSEDIA,
                'default_price_per_hour' => 60000,
                'condition_note' => 'Stand tetap di Cafe Bahagia.',
            ],
        );

        // 4) Sample rentals (lewat repository agar report otomatis terbentuk)
        if (Rental::count() === 0) {
            /** @var RentalRepository $repo */
            $repo = app(RentalRepository::class);

            $repo->createWithReport([
                'playbox_id' => $pbx1->id,
                'user_id' => $admin->id,
                'rental_type' => Rental::TYPE_PRIBADI,
                'rental_date' => now()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '14:00',
                'price_per_hour' => 50000,
                'payment_method' => 'cash',
                'payment_status' => 'lunas',
                'customer_name' => 'Andi',
                'note' => 'Sewa 4 jam di lokasi owner.',
            ]);

            $repo->createWithReport([
                'playbox_id' => $pbx2->id,
                'user_id' => $admin->id,
                'rental_type' => Rental::TYPE_PRIBADI,
                'rental_date' => now()->subDay()->toDateString(),
                'start_time' => '13:00',
                'end_time' => '18:00',
                'price_per_hour' => 45000,
                'payment_method' => 'transfer',
                'payment_status' => 'lunas',
                'customer_name' => 'Budi',
            ]);

            // Kerjasama: durasi 30 jam x 60.000 = 1.800.000 (kurang dari Rp800k? tidak, di atas)
            // Untuk contoh "5jt" pakai durasi panjang harian agregat
            // Kerjasama dgn pendapatan > biaya staff (contoh sehat: 12 jam x 200k = 2,4 juta)
            $repo->createWithReport([
                'playbox_id' => $pbx3->id,
                'partner_id' => $tata->id,
                'user_id' => $admin->id,
                'rental_type' => Rental::TYPE_KERJASAMA,
                'rental_date' => now()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '22:00',
                'price_per_hour' => 200000,
                'payment_method' => 'cash',
                'payment_status' => 'lunas',
                'customer_name' => 'Cafe Tata Krama (akumulasi harian)',
                'note' => 'Akumulasi sewa harian di Cafe Tata Krama.',
            ]);

            // Kerjasama dgn pendapatan kurang dari biaya staff (peringatan)
            $repo->createWithReport([
                'playbox_id' => $pbx4->id,
                'partner_id' => $bahagia->id,
                'user_id' => $admin->id,
                'rental_type' => Rental::TYPE_KERJASAMA,
                'rental_date' => now()->subDays(2)->toDateString(),
                'start_time' => '12:00',
                'end_time' => '23:00',
                'price_per_hour' => 60000,
                'payment_method' => 'qris',
                'payment_status' => 'lunas',
                'customer_name' => 'Cafe Bahagia (hari sepi)',
                'note' => 'Pendapatan < biaya staff: bagi hasil = 0.',
            ]);
        }

        // 5) Sample expenses
        if (Expense::count() === 0) {
            Expense::create([
                'playbox_id' => $pbx1->id,
                'expense_date' => now()->subDays(3)->toDateString(),
                'type' => 'maintenance',
                'amount' => 150000,
                'description' => 'Service controller PlayBox 1.',
            ]);
            Expense::create([
                'partner_id' => $tata->id,
                'expense_date' => now()->subDays(1)->toDateString(),
                'type' => 'staff',
                'amount' => 800000,
                'description' => 'Biaya staff penunggu Cafe Tata Krama.',
            ]);
            Expense::create([
                'playbox_id' => $pbx2->id,
                'expense_date' => now()->subDays(5)->toDateString(),
                'type' => 'kerusakan',
                'amount' => 75000,
                'description' => 'Ganti kabel HDMI.',
            ]);
        }
    }
}
