<?php

namespace Database\Seeders;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $akunPengguna = [
            [
                'name' => 'Superadmin Sistem',
                'email' => 'superadmin@monitoring.test',
                'level_akses' => LevelAksesPengguna::SUPERADMIN,
            ],
            [
                'name' => 'Pengguna Level 1',
                'email' => 'level1@monitoring.test',
                'level_akses' => LevelAksesPengguna::LEVEL_1,
            ],
            [
                'name' => 'Pengguna Level 2',
                'email' => 'level2@monitoring.test',
                'level_akses' => LevelAksesPengguna::LEVEL_2,
            ],
            [
                'name' => 'Pengguna Level 3',
                'email' => 'level3@monitoring.test',
                'level_akses' => LevelAksesPengguna::LEVEL_3,
            ],
            [
                'name' => 'Pengguna Level 4',
                'email' => 'level4@monitoring.test',
                'level_akses' => LevelAksesPengguna::LEVEL_4,
            ],
            [
                'name' => 'Pengguna Level 5',
                'email' => 'level5@monitoring.test',
                'level_akses' => LevelAksesPengguna::LEVEL_5,
            ],
        ];

        foreach ($akunPengguna as $akun) {
            User::updateOrCreate(
                ['email' => $akun['email']],
                [
                    'name' => $akun['name'],
                    'password' => 'password',
                    'level_akses' => $akun['level_akses'],
                    'email_verified_at' => now(),
                ],
            );
        }

        $this->call(DataContohSeeder::class);
    }
}
