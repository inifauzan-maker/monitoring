<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_membuka_halaman_progres_belajar_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Monitoring',
        ]);
        MateriKursus::factory()->create([
            'kursus_id' => $kursus->id,
            'judul' => 'Materi Dasar Monitoring',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.progres_belajar'));

        $response
            ->assertOk()
            ->assertSee('Progres Belajar')
            ->assertSee('Pantau progres belajar per materi')
            ->assertSee('Materi Dasar Monitoring');
    }
}
