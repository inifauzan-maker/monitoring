<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_pengguna', function (Blueprint $table) {
            $table->unsignedBigInteger('total_klik')->default(0)->after('aktif');
            $table->timestamp('terakhir_diklik_pada')->nullable()->after('total_klik');
        });
    }

    public function down(): void
    {
        Schema::table('link_pengguna', function (Blueprint $table) {
            $table->dropColumn(['total_klik', 'terakhir_diklik_pada']);
        });
    }
};
