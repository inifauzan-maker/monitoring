<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_soal_lms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kursus_id')->nullable()->constrained('kursus')->nullOnDelete();
            $table->text('pertanyaan');
            $table->json('opsi_jawaban');
            $table->unsignedTinyInteger('indeks_jawaban_benar');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_soal_lms');
    }
};
