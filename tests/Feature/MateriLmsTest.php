<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\ProgresBelajarMateri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_materi_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Laravel Dasar',
        ]);

        MateriKursus::factory()->create([
            'kursus_id' => $kursus->id,
            'judul' => 'Pengenalan Laravel',
            'youtube_id' => 'video-laravel-001',
            'urutan' => 1,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.materi'));

        $response
            ->assertOk()
            ->assertSee('Kelola lesson atau video materi')
            ->assertSee('Pengenalan Laravel')
            ->assertSee('Kursus Laravel Dasar');
    }

    public function test_pengguna_non_superadmin_tidak_bisa_menambah_materi_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $kursus = Kursus::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.materi.store'), [
                'kursus_id' => $kursus->id,
                'judul' => 'Materi Monitoring',
                'youtube_id' => 'monitor001',
                'durasi_detik' => 600,
                'urutan' => 1,
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_materi_lms(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Monitoring Dasar',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.materi.store'), [
                'kursus_id' => $kursus->id,
                'judul' => 'Pengenalan Monitoring',
                'youtube_id' => 'monitoring001',
                'durasi_detik' => 480,
            ]);

        $response->assertRedirect(route('lms.materi'));

        $this->assertDatabaseHas('materi_kursus', [
            'kursus_id' => $kursus->id,
            'judul' => 'Pengenalan Monitoring',
            'youtube_id' => 'monitoring001',
        ]);

        $idMateri = MateriKursus::query()->where('youtube_id', 'monitoring001')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'materi',
            'aksi' => 'tambah',
            'subjek_id' => $idMateri,
        ]);
    }

    public function test_pengguna_bisa_memfilter_materi_berdasarkan_kursus(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursusA = Kursus::factory()->create(['judul' => 'Kursus A']);
        $kursusB = Kursus::factory()->create(['judul' => 'Kursus B']);

        MateriKursus::factory()->create([
            'kursus_id' => $kursusA->id,
            'judul' => 'Materi Kursus A',
        ]);

        MateriKursus::factory()->create([
            'kursus_id' => $kursusB->id,
            'judul' => 'Materi Kursus B',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.materi', ['kursus' => $kursusA->id]));

        $response
            ->assertOk()
            ->assertSee('Materi Kursus A')
            ->assertDontSee('Materi Kursus B');
    }

    public function test_pengguna_bisa_membuka_halaman_detail_materi_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Video Monitoring',
        ]);
        $materi = MateriKursus::factory()->create([
            'kursus_id' => $kursus->id,
            'judul' => 'Menonton Dashboard',
            'youtube_id' => 'yt-dashboard-001',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.materi.show', $materi));

        $response
            ->assertOk()
            ->assertSee('Video Tutorial')
            ->assertSee('Menonton Dashboard')
            ->assertSee('Kursus Video Monitoring')
            ->assertSee('Sinkron progres otomatis aktif');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'materi',
            'aksi' => 'lihat',
            'subjek_id' => $materi->id,
        ]);
    }

    public function test_pengguna_bisa_sinkron_progres_otomatis_dari_halaman_materi(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $materi = MateriKursus::factory()->create([
            'durasi_detik' => 800,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->postJson(route('lms.materi.progres', $materi), [
                'detik_terakhir' => 400,
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'persen_progres' => 50,
                'detik_terakhir' => 400,
                'label_status' => 'Berjalan',
            ]);

        $this->assertDatabaseHas('progres_belajar_materi', [
            'user_id' => $pengguna->id,
            'materi_kursus_id' => $materi->id,
            'detik_terakhir' => 400,
            'persen_progres' => 50,
        ]);

        $this->assertDatabaseMissing('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'progres_belajar',
            'subjek_id' => ProgresBelajarMateri::query()
                ->where('user_id', $pengguna->id)
                ->where('materi_kursus_id', $materi->id)
                ->value('id'),
        ]);
    }
}
