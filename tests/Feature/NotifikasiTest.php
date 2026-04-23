<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Artikel;
use App\Models\NotifikasiPengguna;
use App\Models\Proyek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_membuka_halaman_notifikasi(): void
    {
        $pengguna = User::factory()->create();
        NotifikasiPengguna::factory()->for($pengguna, 'pengguna')->create([
            'judul' => 'Tugas baru untuk Anda',
            'pesan' => 'Silakan cek detail tugas terbaru.',
            'tipe' => 'info',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('notifikasi.index'));

        $response
            ->assertOk()
            ->assertSee('Notifikasi')
            ->assertSee('Tugas baru untuk Anda')
            ->assertSee('Silakan cek detail tugas terbaru.');
    }

    public function test_pengguna_bisa_menandai_notifikasi_sudah_dibaca(): void
    {
        $pengguna = User::factory()->create();
        $notifikasi = NotifikasiPengguna::factory()->for($pengguna, 'pengguna')->create();

        $response = $this
            ->actingAs($pengguna)
            ->patch(route('notifikasi.baca', $notifikasi), [
                'redirect_ke' => route('notifikasi.index'),
            ]);

        $response->assertRedirect(route('notifikasi.index'));

        $this->assertNotNull($notifikasi->fresh()->dibaca_pada);
    }

    public function test_pengguna_bisa_menandai_semua_notifikasi_sudah_dibaca(): void
    {
        $pengguna = User::factory()->create();
        NotifikasiPengguna::factory()->count(2)->for($pengguna, 'pengguna')->create();

        $response = $this
            ->actingAs($pengguna)
            ->patch(route('notifikasi.baca_semua'), [
                'redirect_ke' => route('notifikasi.index'),
            ]);

        $response->assertRedirect(route('notifikasi.index'));

        $this->assertDatabaseCount('notifikasi_pengguna', 2);
        $this->assertSame(0, $pengguna->notifikasiPengguna()->whereNull('dibaca_pada')->count());
    }

    public function test_pengguna_tidak_bisa_menandai_notifikasi_pengguna_lain(): void
    {
        $pengguna = User::factory()->create();
        $penggunaLain = User::factory()->create();
        $notifikasi = NotifikasiPengguna::factory()->for($penggunaLain, 'pengguna')->create();

        $response = $this
            ->actingAs($pengguna)
            ->patch(route('notifikasi.baca', $notifikasi), [
                'redirect_ke' => route('notifikasi.index'),
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_menambah_tugas_ke_pic_membuat_notifikasi(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $pic = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $proyek = Proyek::factory()->create([
            'dibuat_oleh' => $superadmin->id,
            'penanggung_jawab_id' => $superadmin->id,
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->post(route('proyek.tugas.store'), [
                'proyek_id' => $proyek->id,
                'judul_tugas' => 'Validasi alur notifikasi tugas',
                'status_tugas' => 'berjalan',
                'prioritas_tugas' => 'tinggi',
                'persentase_progres' => 25,
                'penanggung_jawab_id' => $pic->id,
                'tanggal_mulai' => '2026-04-23',
                'tanggal_target' => '2026-04-25',
                'deskripsi_tugas' => 'Pastikan PIC menerima notifikasi baru.',
                'catatan_tugas' => 'Diprioritaskan untuk pengujian internal.',
            ]);

        $response->assertRedirect(route('proyek.detail_tugas'));

        $this->assertDatabaseHas('notifikasi_pengguna', [
            'user_id' => $pic->id,
            'judul' => 'Tugas baru untuk Anda',
        ]);
    }

    public function test_penulis_menerbitkan_artikel_membuat_notifikasi_ke_superadmin(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $penulis = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $artikel = Artikel::factory()->for($penulis, 'penulis')->create([
            'sudah_diterbitkan' => false,
            'diterbitkan_pada' => null,
        ]);

        $response = $this
            ->actingAs($penulis)
            ->post(route('tools.artikel.terbitkan', $artikel));

        $response->assertRedirect(route('tools.artikel.edit', $artikel));

        $this->assertDatabaseHas('notifikasi_pengguna', [
            'user_id' => $superadmin->id,
            'judul' => 'Artikel diterbitkan',
        ]);
    }
}
