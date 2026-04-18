<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statistik_link_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('link_pengguna_id')->nullable()->constrained('link_pengguna')->nullOnDelete();
            $table->string('jenis_aktivitas', 40);
            $table->date('tanggal');
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'tanggal']);
            $table->index(['user_id', 'jenis_aktivitas', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistik_link_harian');
    }
};
