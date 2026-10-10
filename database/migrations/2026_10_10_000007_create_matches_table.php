<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->cascadeOnDelete();
            $table->unsignedInteger('nomor_pertandingan')->nullable();
            $table->string('fase')->default('grup');
            $table->string('babak')->nullable();
            $table->foreignId('tim_a_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('tim_b_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('wasit_id')->nullable()->constrained('referees')->nullOnDelete();
            $table->date('tanggal')->nullable();
            $table->time('jam')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status')->default('dijadwalkan');
            $table->unsignedInteger('skor_a')->default(0);
            $table->unsignedInteger('skor_b')->default(0);
            $table->unsignedInteger('skor_penalti_a')->nullable();
            $table->unsignedInteger('skor_penalti_b')->nullable();
            $table->foreignId('pemenang_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('next_match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->string('next_slot')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
