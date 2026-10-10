<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            $table->string('logo')->nullable();
            $table->string('pelatih')->nullable();
            $table->string('status_pendaftaran')->default('terdaftar');
            $table->timestamps();

            $table->unique(['tournament_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
