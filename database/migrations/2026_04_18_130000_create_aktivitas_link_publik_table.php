<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_link_publik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('link_pengguna_id')->nullable()->constrained('link_pengguna')->nullOnDelete();
            $table->string('jenis_aktivitas', 40);
            $table->string('session_id', 100)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('sumber_traffic', 120)->nullable()->index();
            $table->string('url_tujuan')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'jenis_aktivitas', 'created_at']);
            $table->index(['user_id', 'sumber_traffic', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_link_publik');
    }
};
