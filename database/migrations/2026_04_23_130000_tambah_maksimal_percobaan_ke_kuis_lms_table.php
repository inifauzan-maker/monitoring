<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuis_lms', function (Blueprint $table) {
            $table->unsignedSmallInteger('maksimal_percobaan')->nullable()->after('durasi_menit');
        });
    }

    public function down(): void
    {
        Schema::table('kuis_lms', function (Blueprint $table) {
            $table->dropColumn('maksimal_percobaan');
        });
    }
};
