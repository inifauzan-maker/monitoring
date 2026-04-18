<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KampanyeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_membuka_seluruh_halaman_placeholder_kampanye(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $halaman = [
            'kampanye.ads_iklan' => 'Ads/Iklan',
            'kampanye.media_sosial' => 'Media Sosial',
            'kampanye.website' => 'Website',
            'kampanye.youtube' => 'Youtube',
            'kampanye.event' => 'Event',
            'kampanye.buzzer' => 'Buzzer',
        ];

        foreach ($halaman as $route => $judul) {
            $response = $this
                ->actingAs($pengguna)
                ->get(route($route));

            $response
                ->assertOk()
                ->assertSee($judul)
                ->assertSee('masih dikosongkan sementara');
        }
    }
}
