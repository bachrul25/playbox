<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playbox_id')->nullable()->constrained('playboxes')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->date('expense_date');
            $table->enum('type', ['maintenance', 'perawatan', 'kerusakan', 'staff', 'lainnya']);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['expense_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
