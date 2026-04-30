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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_unit_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->decimal('hourly_price', 14, 2)->default(0);
            $table->decimal('total_price', 14, 2)->default(0);
            $table->string('payment_method')->default('Cash');
            $table->enum('status', ['active', 'finished', 'cancelled'])->default('active');
            $table->string('mode')->default('open'); // open / fixed
            $table->integer('planned_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
