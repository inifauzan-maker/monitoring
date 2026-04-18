<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_item_id')->nullable()->constrained('produk_items')->nullOnDelete();
            $table->foreignId('divalidasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_lengkap', 150);
            $table->string('asal_sekolah', 150)->nullable();
            $table->string('tingkat_kelas', 50)->nullable();
            $table->string('jurusan', 100)->nullable();
            $table->string('nomor_telepon', 32)->nullable();
            $table->string('nama_orang_tua', 150)->nullable();
            $table->string('nomor_telepon_orang_tua', 32)->nullable();
            $table->string('lokasi_belajar', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('program', 50)->nullable();
            $table->string('nama_program', 120)->nullable();
            $table->string('sistem_pembayaran', 20)->default('lunas');
            $table->string('status_validasi', 20)->default('pending');
            $table->dateTime('tanggal_validasi')->nullable();
            $table->string('nomor_invoice', 50)->nullable();
            $table->unsignedInteger('total_invoice')->nullable();
            $table->unsignedInteger('jumlah_pembayaran')->default(0);
            $table->unsignedInteger('sisa_tagihan')->nullable();
            $table->string('status_pembayaran', 20)->default('belum_bayar');
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['program', 'status_validasi', 'status_pembayaran']);
            $table->index(['lokasi_belajar', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_siswa');
    }
};
