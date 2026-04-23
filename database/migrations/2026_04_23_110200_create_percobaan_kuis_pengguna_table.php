<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('percobaan_kuis_pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_lms_id')->constrained('kuis_lms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('percobaan_ke');
            $table->json('paket_soal')->nullable();
            $table->json('jawaban_pengguna')->nullable();
            $table->unsignedInteger('jumlah_benar')->default(0);
            $table->unsignedInteger('jumlah_pertanyaan')->default(0);
            $table->unsignedInteger('skor')->default(0);
            $table->boolean('lulus')->default(false);
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
            $table->unique(['kuis_lms_id', 'user_id', 'percobaan_ke'], 'percobaan_kuis_pengguna_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percobaan_kuis_pengguna');
    }
};
