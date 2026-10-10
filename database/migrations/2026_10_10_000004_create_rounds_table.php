<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_round_id')->nullable()->constrained('rounds')->nullOnDelete();
            $table->unsignedInteger('nomor');
            $table->string('nama');
            $table->string('tipe')->default('grup');
            $table->boolean('lanjutan_dibuat')->default(false);
            $table->timestamps();

            $table->unique(['tournament_id', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
