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
        Schema::create('proyek', function (Blueprint $table) {
            $table->id();
            $table->string('kode_project', 50)->unique();
            $table->string('nama_project', 150);
            $table->string('klien', 150)->nullable();
            $table->string('status_project', 30)->default('perencanaan');
            $table->string('prioritas_project', 20)->default('sedang');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_target_selesai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('deskripsi_project')->nullable();
            $table->text('alur_kerja')->nullable();
            $table->text('sop_ringkas')->nullable();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('skor_evaluasi')->nullable();
            $table->text('catatan_evaluasi')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyek');
    }
};
