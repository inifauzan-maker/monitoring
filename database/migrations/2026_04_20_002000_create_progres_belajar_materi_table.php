<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres_belajar_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('materi_kursus_id')->constrained('materi_kursus')->cascadeOnDelete();
            $table->unsignedInteger('detik_terakhir')->default(0);
            $table->unsignedTinyInteger('persen_progres')->default(0);
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'materi_kursus_id'], 'progres_belajar_unik_pengguna_materi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_belajar_materi');
    }
};
