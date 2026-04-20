<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\HistoriProgresTugas;
use App\Models\Proyek;
use App\Models\TugasProyek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyekTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_membuka_seluruh_halaman_modul_proyek(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $halaman = [
            'proyek.daftar_project' => 'Daftar Project',
            'proyek.detail_tugas' => 'Detail Tugas',
            'proyek.alur_sop' => 'Alur & SOP',
            'proyek.penanggung_jawab' => 'Penanggung Jawab',
            'proyek.progres' => 'Progres',
            'proyek.evaluasi' => 'Evaluasi',
            'proyek.pelaporan' => 'Pelaporan',
        ];

        foreach ($halaman as $route => $judul) {
            $response = $this
                ->actingAs($pengguna)
                ->get(route($route));

            $response
                ->assertOk()
                ->assertSee($judul);
        }
    }

    public function test_superadmin_bisa_menambah_project(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $pic = User::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('proyek.store'), [
                'kode_project' => 'PRJ-001',
                'nama_project' => 'Implementasi Monitoring',
                'klien' => 'Internal',
                'status_project' => 'berjalan',
                'prioritas_project' => 'tinggi',
                'penanggung_jawab_id' => $pic->id,
                'tanggal_mulai' => '2026-04-19',
                'tanggal_target_selesai' => '2026-05-19',
                'deskripsi_project' => 'Pengerjaan modul monitoring utama.',
                'alur_kerja' => "Analisis\nEksekusi\nReview",
                'sop_ringkas' => "Susun backlog\nKerjakan per sprint",
                'skor_evaluasi' => 88,
                'catatan_evaluasi' => 'Perlu pantauan mingguan.',
            ]);

        $response->assertRedirect(route('proyek.daftar_project'));

        $this->assertDatabaseHas('proyek', [
            'kode_project' => 'PRJ-001',
            'nama_project' => 'Implementasi Monitoring',
            'penanggung_jawab_id' => $pic->id,
            'dibuat_oleh' => $pengguna->id,
        ]);
    }

    public function test_pengguna_non_superadmin_tidak_bisa_menambah_project(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('proyek.store'), [
                'kode_project' => 'PRJ-002',
                'nama_project' => 'Project Tanpa Akses',
                'status_project' => 'perencanaan',
                'prioritas_project' => 'sedang',
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_tugas_project_dan_status_selesai_dinormalisasi(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $pic = User::factory()->create();
        $proyek = Proyek::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('proyek.tugas.store'), [
                'proyek_id' => $proyek->id,
                'judul_tugas' => 'Susun laporan mingguan',
                'status_tugas' => 'selesai',
                'prioritas_tugas' => 'tinggi',
                'persentase_progres' => 70,
                'penanggung_jawab_id' => $pic->id,
                'tanggal_mulai' => '2026-04-19',
                'tanggal_target' => '2026-04-21',
                'deskripsi_tugas' => 'Rekap progres dan hambatan.',
                'catatan_tugas' => 'Sudah dikirim ke tim.',
            ]);

        $response->assertRedirect(route('proyek.detail_tugas'));

        $this->assertDatabaseHas('tugas_proyek', [
            'proyek_id' => $proyek->id,
            'judul_tugas' => 'Susun laporan mingguan',
            'status_tugas' => 'selesai',
            'persentase_progres' => 100,
            'penanggung_jawab_id' => $pic->id,
        ]);

        $idTugas = TugasProyek::query()
            ->where('proyek_id', $proyek->id)
            ->where('judul_tugas', 'Susun laporan mingguan')
            ->value('id');

        $this->assertDatabaseHas('histori_progres_tugas', [
            'tugas_proyek_id' => $idTugas,
            'status_sesudah' => 'selesai',
            'progres_sesudah' => 100,
        ]);
        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'tugas_proyek',
            'aksi' => 'tambah',
            'subjek_id' => $idTugas,
        ]);
    }

    public function test_halaman_detail_tugas_menampilkan_board_status_dan_aksi_cepat_superadmin(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $proyek = Proyek::factory()->create([
            'nama_project' => 'Project Board Internal',
        ]);

        TugasProyek::factory()->create([
            'proyek_id' => $proyek->id,
            'judul_tugas' => 'Susun checklist operasional',
            'status_tugas' => 'review',
            'persentase_progres' => 80,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('proyek.detail_tugas'));

        $response
            ->assertOk()
            ->assertSee('Board Tugas per Status')
            ->assertSee('Susun checklist operasional')
            ->assertSee('Perbarui Cepat');
    }

    public function test_superadmin_bisa_memperbarui_status_tugas_dari_board(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $tugas = TugasProyek::factory()->create([
            'status_tugas' => 'belum_mulai',
            'persentase_progres' => 0,
            'tanggal_mulai' => null,
            'tanggal_selesai' => null,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->patch(route('proyek.tugas.status_cepat', $tugas), [
                'status_tugas' => 'review',
                'persentase_progres' => 30,
            ]);

        $response->assertRedirect(route('proyek.detail_tugas'));

        $this->assertDatabaseHas('tugas_proyek', [
            'id' => $tugas->id,
            'status_tugas' => 'review',
            'persentase_progres' => 75,
        ]);

        $this->assertNotNull($tugas->fresh()->tanggal_mulai);
        $this->assertDatabaseHas('histori_progres_tugas', [
            'tugas_proyek_id' => $tugas->id,
            'status_sebelum' => 'belum_mulai',
            'status_sesudah' => 'review',
            'progres_sebelum' => 0,
            'progres_sesudah' => 75,
        ]);
        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'tugas_proyek',
            'aksi' => 'status_cepat',
            'subjek_id' => $tugas->id,
        ]);
    }

    public function test_pengguna_non_superadmin_tidak_bisa_memperbarui_status_tugas_dari_board(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $tugas = TugasProyek::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->patch(route('proyek.tugas.status_cepat', $tugas), [
                'status_tugas' => 'selesai',
                'persentase_progres' => 100,
            ]);

        $response->assertForbidden();
    }

    public function test_pengguna_bisa_membuka_histori_progres_tugas(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $tugas = TugasProyek::factory()->create([
            'judul_tugas' => 'Validasi laporan harian',
        ]);

        HistoriProgresTugas::query()->create([
            'tugas_proyek_id' => $tugas->id,
            'user_id' => $pengguna->id,
            'status_sebelum' => 'belum_mulai',
            'status_sesudah' => 'berjalan',
            'progres_sebelum' => 0,
            'progres_sesudah' => 35,
            'catatan_histori' => 'Mulai dikerjakan.',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('proyek.tugas.histori', $tugas));

        $response
            ->assertOk()
            ->assertSee('Histori Progres Tugas')
            ->assertSee('Validasi laporan harian')
            ->assertSee('Mulai dikerjakan.');
    }

    public function test_halaman_progres_bisa_difilter_berdasarkan_status_project(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $projectBerjalan = Proyek::factory()->create([
            'nama_project' => 'Project Berjalan Alpha',
            'status_project' => 'berjalan',
        ]);
        $projectSelesai = Proyek::factory()->create([
            'nama_project' => 'Project Final Beta',
            'status_project' => 'selesai',
        ]);

        TugasProyek::factory()->create([
            'proyek_id' => $projectBerjalan->id,
            'judul_tugas' => 'Tugas Berjalan',
            'status_tugas' => 'berjalan',
            'persentase_progres' => 40,
        ]);

        TugasProyek::factory()->create([
            'proyek_id' => $projectSelesai->id,
            'judul_tugas' => 'Tugas Selesai',
            'status_tugas' => 'selesai',
            'persentase_progres' => 100,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('proyek.progres', ['status_project' => 'berjalan']));

        $response
            ->assertOk()
            ->assertSee('Project Berjalan Alpha')
            ->assertDontSee('Project Final Beta');
    }
}
