<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemetaan_beranda_level', function (Blueprint $table) {
            $table->id();
            $table->string('level_akses')->unique();
            $table->string('profil_beranda');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemetaan_beranda_level');
    }
};
