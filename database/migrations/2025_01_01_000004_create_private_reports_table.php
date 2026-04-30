<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->decimal('total_income', 14, 2)->default(0);
            $table->decimal('maintenance_amount', 14, 2)->default(0);
            $table->decimal('owner_profit', 14, 2)->default(0);
            $table->decimal('maintenance_percentage', 5, 2)->default(20);
            $table->decimal('owner_percentage', 5, 2)->default(80);
            $table->date('report_date');
            $table->timestamps();

            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_reports');
    }
};
