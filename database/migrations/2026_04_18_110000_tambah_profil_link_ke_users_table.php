<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug_link', 40)->nullable()->unique()->after('email');
            $table->string('judul_link', 80)->nullable()->after('slug_link');
            $table->text('bio_link')->nullable()->after('judul_link');
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $pengguna): void {
                $dasar = Str::slug((string) $pengguna->name);

                if ($dasar === '') {
                    $dasar = 'pengguna';
                }

                $slug = Str::limit($dasar, 28, '').'-'.$pengguna->id;

                DB::table('users')
                    ->where('id', $pengguna->id)
                    ->update([
                        'slug_link' => $slug,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug_link']);
            $table->dropColumn(['slug_link', 'judul_link', 'bio_link']);
        });
    }
};
