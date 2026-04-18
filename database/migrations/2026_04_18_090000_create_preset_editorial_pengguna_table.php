<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preset_editorial_pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_preset');
            $table->json('konfigurasi_filter');
            $table->timestamps();

            $table->unique(['user_id', 'nama_preset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preset_editorial_pengguna');
    }
};
