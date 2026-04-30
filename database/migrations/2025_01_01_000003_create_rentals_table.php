<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('playbox_id')->constrained('playboxes')->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('rental_type', ['pribadi', 'kerjasama']);
            $table->date('rental_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('duration', 8, 2)->default(0); // jam
            $table->decimal('price_per_hour', 12, 2)->default(0);
            $table->decimal('total_income', 14, 2)->default(0);
            $table->enum('payment_method', ['cash', 'transfer', 'qris'])->default('cash');
            $table->enum('payment_status', ['lunas', 'belum_lunas'])->default('lunas');
            $table->string('customer_name')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['rental_date', 'rental_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
