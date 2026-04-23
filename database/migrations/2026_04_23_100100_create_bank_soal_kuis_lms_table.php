<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_soal_kuis_lms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_lms_id')->constrained('kuis_lms')->cascadeOnDelete();
            $table->foreignId('bank_soal_lms_id')->constrained('bank_soal_lms')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
            $table->unique(['kuis_lms_id', 'bank_soal_lms_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_soal_kuis_lms');
    }
};
