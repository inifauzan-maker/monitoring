<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
            $table->foreignId('penulis_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipe_pemicu', 40);
            $table->json('snapshot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_revisi');
    }
};
