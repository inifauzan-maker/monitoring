<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Enums\ProfilBerandaPengguna;
use App\Models\PemetaanBerandaLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemetaanBerandaTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_bisa_membuka_halaman_pemetaan_beranda(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($superadmin)
            ->get(route('administrasi.pemetaan_beranda.index'));

        $response
            ->assertOk()
            ->assertSee('Pemetaan Beranda')
            ->assertSee('Admin')
            ->assertSee('Staff')
            ->assertSee('Konten & Distribusi')
            ->assertSee('Preview Profil')
            ->assertSee('Indikator utama')
            ->assertSee('Jalur Cepat Konten');
    }

    public function test_non_superadmin_tidak_bisa_membuka_halaman_pemetaan_beranda(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('administrasi.pemetaan_beranda.index'));

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_memperbarui_pemetaan_beranda_per_level(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($superadmin)
            ->put(route('administrasi.pemetaan_beranda.update'), [
                'profil' => [
                    LevelAksesPengguna::LEVEL_1->value => ProfilBerandaPengguna::KOORDINASI->value,
                    LevelAksesPengguna::LEVEL_2->value => ProfilBerandaPengguna::OPERASIONAL->value,
                    LevelAksesPengguna::LEVEL_3->value => ProfilBerandaPengguna::KONTEN->value,
                    LevelAksesPengguna::LEVEL_4->value => ProfilBerandaPengguna::PERSONAL->value,
                    LevelAksesPengguna::LEVEL_5->value => ProfilBerandaPengguna::EKSEKUSI->value,
                ],
            ]);

        $response
            ->assertRedirect(route('administrasi.pemetaan_beranda.index'))
            ->assertSessionHas('status', 'Pemetaan profil beranda berhasil diperbarui.');

        $this->assertDatabaseHas('pemetaan_beranda_level', [
            'level_akses' => LevelAksesPengguna::LEVEL_3->value,
            'profil_beranda' => ProfilBerandaPengguna::KONTEN->value,
        ]);

        $this->assertDatabaseHas('pemetaan_beranda_level', [
            'level_akses' => LevelAksesPengguna::LEVEL_4->value,
            'profil_beranda' => ProfilBerandaPengguna::PERSONAL->value,
        ]);
    }
}
