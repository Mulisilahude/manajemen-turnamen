<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->string('kode_pertandingan')->nullable()->after('nomor_pertandingan');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->boolean('tampil_publik')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('kode_pertandingan');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('tampil_publik');
        });
    }
};
