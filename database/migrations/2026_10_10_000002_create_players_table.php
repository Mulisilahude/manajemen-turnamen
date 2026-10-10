<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->unsignedInteger('nomor_punggung')->nullable();
            $table->string('posisi')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'nomor_punggung']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
