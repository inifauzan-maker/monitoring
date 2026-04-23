<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_kuis_pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_lms_id')->constrained('kuis_lms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('jawaban_pengguna')->nullable();
            $table->unsignedSmallInteger('jumlah_benar')->default(0);
            $table->unsignedSmallInteger('jumlah_pertanyaan')->default(0);
            $table->unsignedTinyInteger('skor')->default(0);
            $table->boolean('lulus')->default(false);
            $table->unsignedSmallInteger('jumlah_percobaan')->default(1);
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->unique(['kuis_lms_id', 'user_id'], 'hasil_kuis_unik_pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_kuis_pengguna');
    }
};
