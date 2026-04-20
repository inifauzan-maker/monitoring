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
        Schema::create('histori_progres_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_proyek_id')->constrained('tugas_proyek')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status_sebelum', 30)->nullable();
            $table->string('status_sesudah', 30);
            $table->unsignedTinyInteger('progres_sebelum')->nullable();
            $table->unsignedTinyInteger('progres_sesudah');
            $table->text('catatan_histori')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_progres_tugas');
    }
};
