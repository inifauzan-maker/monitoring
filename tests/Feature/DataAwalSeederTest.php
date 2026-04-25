<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DataAwalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_default_hanya_membuat_admin_awal_tanpa_data_contoh(): void
    {
        Config::set('data_awal.admin', [
            'name' => 'Admin Produksi',
            'email' => 'admin@simarketing.local',
            'password' => 'password',
            'level_akses' => 'superadmin',
        ]);
        Config::set('data_awal.seed_data_contoh', false);

        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Produksi',
            'email' => 'admin@simarketing.local',
        ]);
        $this->assertDatabaseCount('produk_items', 0);
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('kursus', 0);
    }

    public function test_command_bersihkan_data_contoh_mengosongkan_modul_dan_menghapus_akun_demo(): void
    {
        Config::set('data_awal.admin', [
            'name' => 'Admin Produksi',
            'email' => 'admin@simarketing.local',
            'password' => 'password',
            'level_akses' => 'superadmin',
        ]);
        Config::set('data_awal.seed_data_contoh', true);

        $this->seed();

        $this->assertDatabaseCount('users', 6);
        $this->assertDatabaseCount('produk_items', 3);
        $this->assertDatabaseCount('leads', 3);
        $this->assertDatabaseCount('kursus', 2);

        $this->artisan('app:bersihkan-data-contoh', [
            '--force' => true,
            '--hapus-akun-demo' => true,
        ])->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@simarketing.local',
        ]);
        $this->assertDatabaseCount('produk_items', 0);
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('data_siswa', 0);
        $this->assertDatabaseCount('kursus', 0);
        $this->assertDatabaseCount('materi_kursus', 0);
        $this->assertDatabaseCount('kuis_lms', 0);
        $this->assertDatabaseCount('proyek', 0);
        $this->assertDatabaseCount('artikel', 0);
        $this->assertDatabaseCount('notifikasi_pengguna', 0);
    }
}
