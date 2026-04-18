<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->text('outline_seo')->nullable()->after('deskripsi_seo');
            $table->json('checklist_kesiapan')->nullable()->after('outline_seo');
        });
    }

    public function down(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->dropColumn(['outline_seo', 'checklist_kesiapan']);
        });
    }
};
