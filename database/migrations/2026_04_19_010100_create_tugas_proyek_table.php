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
        Schema::create('tugas_proyek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyek_id')->constrained('proyek')->cascadeOnDelete();
            $table->string('judul_tugas', 180);
            $table->text('deskripsi_tugas')->nullable();
            $table->string('status_tugas', 30)->default('belum_mulai');
            $table->string('prioritas_tugas', 20)->default('sedang');
            $table->unsignedTinyInteger('persentase_progres')->default(0);
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_target')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan_tugas')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_proyek');
    }
};
