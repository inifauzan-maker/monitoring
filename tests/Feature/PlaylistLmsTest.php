<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Kursus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaylistLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_playlist_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.playlist'));

        $response
            ->assertOk()
            ->assertSee('Import Playlist YouTube')
            ->assertSee('Import playlist hanya untuk superadmin');
    }

    public function test_pengguna_non_superadmin_tidak_bisa_import_playlist_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $kursus = Kursus::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.playlist.store'), [
                'kursus_id' => $kursus->id,
                'playlist_url' => 'PL1234567890',
                'durasi_default_detik' => 600,
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_import_playlist_ke_materi_lms(): void
    {
        Config::set('services.youtube.api_key', 'youtube-key-test');
        Http::fake([
            'https://www.googleapis.com/youtube/v3/playlistItems*' => Http::response([
                'items' => [
                    [
                        'snippet' => ['title' => 'Video Pertama'],
                        'contentDetails' => ['videoId' => 'vid001'],
                    ],
                    [
                        'snippet' => ['title' => 'Video Kedua'],
                        'contentDetails' => ['videoId' => 'vid002'],
                    ],
                ],
            ], 200),
        ]);

        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Monitoring Dasar',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.playlist.store'), [
                'kursus_id' => $kursus->id,
                'playlist_url' => 'https://www.youtube.com/playlist?list=PL1234567890',
                'durasi_default_detik' => 600,
            ]);

        $response->assertRedirect(route('lms.materi', ['kursus' => $kursus->id]));

        $this->assertDatabaseHas('materi_kursus', [
            'kursus_id' => $kursus->id,
            'judul' => 'Video Pertama',
            'youtube_id' => 'vid001',
            'durasi_detik' => 600,
            'urutan' => 1,
        ]);

        $this->assertDatabaseHas('materi_kursus', [
            'kursus_id' => $kursus->id,
            'judul' => 'Video Kedua',
            'youtube_id' => 'vid002',
            'durasi_detik' => 600,
            'urutan' => 2,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'playlist',
            'aksi' => 'impor',
            'subjek_id' => $kursus->id,
        ]);
    }

    public function test_import_playlist_melewati_video_duplikat_dalam_kursus_yang_sama(): void
    {
        Config::set('services.youtube.api_key', 'youtube-key-test');
        Http::fake([
            'https://www.googleapis.com/youtube/v3/playlistItems*' => Http::response([
                'items' => [
                    [
                        'snippet' => ['title' => 'Video Lama'],
                        'contentDetails' => ['videoId' => 'vid001'],
                    ],
                    [
                        'snippet' => ['title' => 'Video Baru'],
                        'contentDetails' => ['videoId' => 'vid002'],
                    ],
                ],
            ], 200),
        ]);

        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create();
        $kursus->materiKursus()->create([
            'judul' => 'Video Lama',
            'youtube_id' => 'vid001',
            'durasi_detik' => 300,
            'urutan' => 1,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.playlist.store'), [
                'kursus_id' => $kursus->id,
                'playlist_url' => 'PL1234567890',
                'durasi_default_detik' => 600,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('materi_kursus', 2);
        $this->assertDatabaseHas('materi_kursus', [
            'kursus_id' => $kursus->id,
            'youtube_id' => 'vid002',
            'urutan' => 2,
        ]);
    }
}
