<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('domain_kustom_link', 120)->nullable()->unique()->after('tema_link');
            $table->timestamp('domain_kustom_terhubung_pada')->nullable()->after('domain_kustom_link');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['domain_kustom_link']);
            $table->dropColumn([
                'domain_kustom_link',
                'domain_kustom_terhubung_pada',
            ]);
        });
    }
};
