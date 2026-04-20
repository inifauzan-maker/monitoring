<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAktivitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_bisa_membuka_halaman_log_aktivitas(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        LogAktivitas::query()->create([
            'user_id' => $pengguna->id,
            'modul' => 'autentikasi',
            'aksi' => 'masuk',
            'deskripsi' => 'Pengguna berhasil masuk ke aplikasi.',
            'subjek_tipe' => 'users',
            'subjek_id' => $pengguna->id,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('administrasi.log_aktivitas.index'));

        $response
            ->assertOk()
            ->assertSee('Log Aktivitas')
            ->assertSee('Daftar Aktivitas')
            ->assertSee('Pengguna berhasil masuk ke aplikasi.');
    }

    public function test_non_superadmin_tidak_bisa_membuka_halaman_log_aktivitas(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('administrasi.log_aktivitas.index'));

        $response->assertForbidden();
    }

    public function test_login_berhasil_dicatat_ke_log_aktivitas(): void
    {
        $pengguna = User::factory()->create([
            'email' => 'superadmin@example.test',
        ]);

        $response = $this->post(route('sesi.store'), [
            'email' => 'superadmin@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.beranda'));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'autentikasi',
            'aksi' => 'masuk',
        ]);
    }

    public function test_login_gagal_dicatat_ke_log_aktivitas(): void
    {
        User::factory()->create([
            'email' => 'gagal@example.test',
        ]);

        $response = $this->from(route('login'))->post(route('sesi.store'), [
            'email' => 'gagal@example.test',
            'password' => 'salah-password',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('log_aktivitas', [
            'modul' => 'autentikasi',
            'aksi' => 'masuk_gagal',
            'user_id' => null,
        ]);
    }

    public function test_superadmin_menambah_pengguna_dan_tercatat_di_log_aktivitas(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($superadmin)
            ->post(route('pengaturan.pengguna.store'), [
                'name' => 'Operator Baru',
                'email' => 'operator.baru@example.test',
                'level_akses' => LevelAksesPengguna::LEVEL_4->value,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('pengaturan.pengguna.index'));

        $idPenggunaBaru = User::query()->where('email', 'operator.baru@example.test')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $superadmin->id,
            'modul' => 'pengguna',
            'aksi' => 'tambah',
            'subjek_id' => $idPenggunaBaru,
        ]);
    }
}
