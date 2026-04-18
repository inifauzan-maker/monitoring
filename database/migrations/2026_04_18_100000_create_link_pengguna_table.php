<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_pengguna', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul', 60);
            $table->string('deskripsi', 120)->nullable();
            $table->string('url');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'aktif']);
            $table->index(['user_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_pengguna');
    }
};
