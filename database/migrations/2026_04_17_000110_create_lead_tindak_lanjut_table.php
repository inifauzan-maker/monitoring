<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->dateTime('jadwal_tindak_lanjut')->nullable();
            $table->string('status', 20)->default('direncanakan');
            $table->timestamps();

            $table->index(['status', 'jadwal_tindak_lanjut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_tindak_lanjut');
    }
};
