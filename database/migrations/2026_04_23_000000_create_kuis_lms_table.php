<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis_lms', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('target_tipe', 20);
            $table->foreignId('kursus_id')->nullable()->constrained('kursus')->nullOnDelete();
            $table->foreignId('materi_kursus_id')->nullable()->constrained('materi_kursus')->nullOnDelete();
            $table->unsignedTinyInteger('nilai_lulus')->default(70);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis_lms');
    }
};
