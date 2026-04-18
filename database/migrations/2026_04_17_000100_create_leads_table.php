<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_siswa', 150);
            $table->string('asal_sekolah', 150)->nullable();
            $table->string('nomor_telepon', 32)->nullable();
            $table->string('channel', 50)->nullable();
            $table->string('sumber', 80)->nullable();
            $table->string('status', 20)->default('prospek');
            $table->dateTime('jadwal_tindak_lanjut')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('kontak_terakhir')->nullable();
            $table->timestamps();

            $table->index(['status', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
