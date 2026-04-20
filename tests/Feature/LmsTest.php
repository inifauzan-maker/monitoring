<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_membuka_seluruh_halaman_placeholder_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $halaman = [
            'lms.kursus' => 'Kursus',
            'lms.materi' => 'Materi',
            'lms.playlist' => 'Playlist',
            'lms.progres_belajar' => 'Progres Belajar',
        ];

        foreach ($halaman as $route => $judul) {
            $response = $this
                ->actingAs($pengguna)
                ->get(route($route));

            $response
                ->assertOk()
                ->assertSee('LMS')
                ->assertSee($judul)
                ->assertSee('masih dikosongkan sementara');
        }
    }
}
