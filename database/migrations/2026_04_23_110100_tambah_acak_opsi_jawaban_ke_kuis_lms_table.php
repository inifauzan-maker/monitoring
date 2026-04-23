<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuis_lms', function (Blueprint $table) {
            $table->boolean('acak_opsi_jawaban')->default(false)->after('acak_soal');
        });
    }

    public function down(): void
    {
        Schema::table('kuis_lms', function (Blueprint $table) {
            $table->dropColumn('acak_opsi_jawaban');
        });
    }
};
