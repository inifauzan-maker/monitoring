<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('headline_link', 120)->nullable()->after('judul_link');
            $table->string('label_cta_link', 40)->nullable()->after('headline_link');
            $table->string('url_cta_link')->nullable()->after('label_cta_link');
            $table->string('tema_link', 20)->default('sunset')->after('url_cta_link');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'headline_link',
                'label_cta_link',
                'url_cta_link',
                'tema_link',
            ]);
        });
    }
};
