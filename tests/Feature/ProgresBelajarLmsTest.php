<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\ProgresBelajarMateri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgresBelajarLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_memperbarui_progres_belajar_sendiri(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $materi = MateriKursus::factory()->create([
            'durasi_detik' => 600,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.progres_belajar.store'), [
                'materi_kursus_id' => $materi->id,
                'detik_terakhir' => 240,
                'persen_progres' => 40,
            ]);

        $response->assertRedirect(route('lms.progres_belajar', ['materi' => $materi->id]));

        $this->assertDatabaseHas('progres_belajar_materi', [
            'user_id' => $pengguna->id,
            'materi_kursus_id' => $materi->id,
            'detik_terakhir' => 240,
            'persen_progres' => 40,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'progres_belajar',
            'aksi' => 'ubah',
        ]);
    }

    public function test_pengguna_bisa_memfilter_progres_belajar_berdasarkan_status(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create();
        $materiSelesai = MateriKursus::factory()->create([
            'kursus_id' => $kursus->id,
            'judul' => 'Materi Selesai',
        ]);
        $materiBelum = MateriKursus::factory()->create([
            'kursus_id' => $kursus->id,
            'judul' => 'Materi Belum',
        ]);

        ProgresBelajarMateri::factory()->create([
            'user_id' => $pengguna->id,
            'materi_kursus_id' => $materiSelesai->id,
            'persen_progres' => 100,
            'selesai_pada' => now(),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.progres_belajar', ['status' => 'selesai']));

        $response
            ->assertOk()
            ->assertSee('Materi Selesai')
            ->assertSee('data-materi-row="'.$materiSelesai->id.'"', false)
            ->assertDontSee('data-materi-row="'.$materiBelum->id.'"', false);
    }

    public function test_superadmin_bisa_memfilter_target_pengguna_pada_progres_belajar(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Superadmin Simarketing',
        ]);
        $penggunaA = User::factory()->create([
            'name' => 'Peserta A',
            'email' => 'peserta-a@example.test',
        ]);
        $penggunaB = User::factory()->create([
            'name' => 'Peserta B',
            'email' => 'peserta-b@example.test',
        ]);

        $materi = MateriKursus::factory()->create([
            'judul' => 'Materi Filter Pengguna',
        ]);

        ProgresBelajarMateri::factory()->create([
            'user_id' => $penggunaA->id,
            'materi_kursus_id' => $materi->id,
            'persen_progres' => 75,
        ]);

        ProgresBelajarMateri::factory()->create([
            'user_id' => $penggunaB->id,
            'materi_kursus_id' => $materi->id,
            'persen_progres' => 10,
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->get(route('lms.progres_belajar', ['pengguna' => $penggunaA->id]));

        $response
            ->assertOk()
            ->assertSee('Peserta A')
            ->assertSee('75%')
            ->assertSee('Mode Pantauan Pengguna');
    }

    public function test_superadmin_bisa_memperbarui_progres_pengguna_lain(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $pengguna = User::factory()->create();
        $materi = MateriKursus::factory()->create([
            'durasi_detik' => 900,
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->post(route('lms.progres_belajar.store'), [
                'pengguna_id' => $pengguna->id,
                'filter_pengguna' => $pengguna->id,
                'materi_kursus_id' => $materi->id,
                'tandai_selesai' => 1,
            ]);

        $response->assertRedirect(route('lms.progres_belajar', [
            'pengguna' => $pengguna->id,
            'materi' => $materi->id,
        ]));

        $this->assertDatabaseHas('progres_belajar_materi', [
            'user_id' => $pengguna->id,
            'materi_kursus_id' => $materi->id,
            'persen_progres' => 100,
            'detik_terakhir' => 900,
        ]);
    }
}
