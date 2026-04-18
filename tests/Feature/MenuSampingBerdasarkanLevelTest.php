<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuSampingBerdasarkanLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_melihat_semua_bagian_menu_penting(): void
    {
        $pengguna = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Pengguna (RBAC)')
            ->assertSee('Artikel')
            ->assertSee('Link')
            ->assertSee('Omzet')
            ->assertSee('Siswa')
            ->assertSee('Leads')
            ->assertSee('Produk')
            ->assertSee('Kampanye')
            ->assertSee('Ads/Iklan')
            ->assertSee('Media Sosial')
            ->assertSee('Website')
            ->assertSee('Youtube')
            ->assertSee('Event')
            ->assertSee('Buzzer');
    }

    public function test_level_lima_melihat_menu_utama_dan_submenu_kampanye_tanpa_menu_rbac(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Artikel')
            ->assertSee('Link')
            ->assertSee('Omzet')
            ->assertSee('Siswa')
            ->assertSee('Leads')
            ->assertSee('Produk')
            ->assertSee('Kampanye')
            ->assertSee('Ads/Iklan')
            ->assertSee('Media Sosial')
            ->assertSee('Website')
            ->assertSee('Youtube')
            ->assertSee('Event')
            ->assertSee('Buzzer')
            ->assertDontSee('Pengguna (RBAC)');
    }

    public function test_level_tiga_tetap_melihat_menu_utama_dan_submenu_kampanye_tanpa_menu_rbac(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Artikel')
            ->assertSee('Link')
            ->assertSee('Omzet')
            ->assertSee('Siswa')
            ->assertSee('Leads')
            ->assertSee('Produk')
            ->assertSee('Kampanye')
            ->assertSee('Ads/Iklan')
            ->assertSee('Media Sosial')
            ->assertSee('Website')
            ->assertSee('Youtube')
            ->assertSee('Event')
            ->assertSee('Buzzer')
            ->assertDontSee('Pengguna (RBAC)');
    }
}
