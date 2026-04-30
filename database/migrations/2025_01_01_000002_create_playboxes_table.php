<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playboxes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('ownership_type', ['pribadi', 'kerjasama'])->default('pribadi');
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('location')->nullable();
            $table->enum('status', ['tersedia', 'disewa', 'maintenance', 'tidak_aktif'])->default('tersedia');
            $table->decimal('default_price_per_hour', 12, 2)->default(0);
            $table->text('condition_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playboxes');
    }
};
