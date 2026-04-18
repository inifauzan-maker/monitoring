<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk_items', function (Blueprint $table) {
            $table->id();
            $table->string('program', 50);
            $table->string('tahun_ajaran', 15)->default('2026 - 2027');
            $table->string('kode_1', 10);
            $table->string('kode_2', 10);
            $table->string('kode_3', 10);
            $table->string('kode_4', 10);
            $table->string('nama', 120);
            $table->unsignedInteger('biaya_daftar');
            $table->unsignedInteger('biaya_pendidikan');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('siswa')->default(0);
            $table->unsignedBigInteger('omzet')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_items');
    }
};
