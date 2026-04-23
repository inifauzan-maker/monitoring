<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuis_lms', function (Blueprint $table) {
            $table->boolean('gunakan_bank_soal')->default(false)->after('nilai_lulus');
            $table->boolean('acak_soal')->default(false)->after('gunakan_bank_soal');
            $table->unsignedInteger('jumlah_soal_tampil')->nullable()->after('acak_soal');
        });
    }

    public function down(): void
    {
        Schema::table('kuis_lms', function (Blueprint $table) {
            $table->dropColumn([
                'gunakan_bank_soal',
                'acak_soal',
                'jumlah_soal_tampil',
            ]);
        });
    }
};
