<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->decimal('total_income', 14, 2)->default(0);
            $table->decimal('staff_cost', 14, 2)->default(800000);
            $table->decimal('net_income', 14, 2)->default(0);
            $table->decimal('owner_share', 14, 2)->default(0);
            $table->decimal('partner_share', 14, 2)->default(0);
            $table->decimal('share_percentage', 5, 2)->default(50);
            $table->date('report_date');
            $table->timestamps();

            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_reports');
    }
};
