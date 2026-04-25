<?php

namespace Database\Seeders;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Database\Seeder;

class AkunAwalSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::emailAdmin()],
            [
                'name' => self::namaAdmin(),
                'password' => self::passwordAdmin(),
                'level_akses' => self::levelAksesAdmin(),
                'email_verified_at' => now(),
            ],
        );
    }

    public static function namaAdmin(): string
    {
        return (string) config('data_awal.admin.name', 'Admin Simarketing');
    }

    public static function emailAdmin(): string
    {
        return (string) config('data_awal.admin.email', 'admin@simarketing.local');
    }

    public static function passwordAdmin(): string
    {
        return (string) config('data_awal.admin.password', 'password');
    }

    public static function levelAksesAdmin(): LevelAksesPengguna
    {
        $nilai = (string) config('data_awal.admin.level_akses', LevelAksesPengguna::SUPERADMIN->value);

        return LevelAksesPengguna::tryFrom($nilai) ?? LevelAksesPengguna::SUPERADMIN;
    }
}
