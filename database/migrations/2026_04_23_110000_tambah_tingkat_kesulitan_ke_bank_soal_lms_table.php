<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_soal_lms', function (Blueprint $table) {
            $table->string('tingkat_kesulitan', 20)->default('menengah')->after('indeks_jawaban_benar');
        });
    }

    public function down(): void
    {
        Schema::table('bank_soal_lms', function (Blueprint $table) {
            $table->dropColumn('tingkat_kesulitan');
        });
    }
};
