<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kata_kunci_utama')->nullable();
            $table->text('ringkasan');
            $table->longText('konten');
            $table->foreignId('kategori_artikel_id')->nullable()->constrained('kategori_artikel')->nullOnDelete();
            $table->foreignId('penulis_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tingkat_keahlian')->default('menengah');
            $table->text('bio_penulis')->nullable();
            $table->json('sumber_referensi')->nullable();
            $table->string('judul_seo')->nullable();
            $table->string('deskripsi_seo', 160)->nullable();
            $table->string('gambar_unggulan_path')->nullable();
            $table->string('alt_gambar_unggulan')->nullable();
            $table->boolean('sudah_diterbitkan')->default(false);
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
