<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi_kursus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kursus_id')->constrained('kursus')->cascadeOnDelete();
            $table->string('judul', 180);
            $table->string('youtube_id', 60);
            $table->unsignedInteger('durasi_detik')->default(0);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_kursus');
    }
};
