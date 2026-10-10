<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->unique(['round_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
