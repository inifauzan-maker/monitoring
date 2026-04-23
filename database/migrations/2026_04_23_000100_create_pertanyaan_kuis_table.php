<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertanyaan_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_lms_id')->constrained('kuis_lms')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->json('opsi_jawaban');
            $table->unsignedTinyInteger('indeks_jawaban_benar');
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_kuis');
    }
};
