<?php

namespace Database\Seeders;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Database\Seeder;

class AkunDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::daftarAkun() as $akun) {
            User::query()->updateOrCreate(
                ['email' => $akun['email']],
                [
                    'name' => $akun['name'],
                    'password' => self::passwordAkunDemo(),
                    'level_akses' => LevelAksesPengguna::from($akun['level_akses']),
                    'email_verified_at' => now(),
                ],
            );
        }
    }

    /**
     * @return array<int, array{name: string, email: string, level_akses: string}>
     */
    public static function daftarAkun(): array
    {
        return array_values(config('data_awal.akun_demo', []));
    }

    public static function emailUntukLevel(LevelAksesPengguna $levelAkses): ?string
    {
        foreach (self::daftarAkun() as $akun) {
            if (($akun['level_akses'] ?? null) === $levelAkses->value) {
                return $akun['email'] ?? null;
            }
        }

        return null;
    }

    public static function passwordAkunDemo(): string
    {
        return (string) config('data_awal.admin.password', 'password');
    }
}
