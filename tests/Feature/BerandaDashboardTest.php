<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Enums\ProfilBerandaPengguna;
use App\Models\Artikel;
use App\Models\DataSiswa;
use App\Models\HasilKuisPengguna;
use App\Models\KuisLms;
use App\Models\Kursus;
use App\Models\Lead;
use App\Models\LogAktivitas;
use App\Models\MateriKursus;
use App\Models\NotifikasiPengguna;
use App\Models\ProdukItem;
use App\Models\ProgresBelajarMateri;
use App\Models\Proyek;
use App\Models\TugasProyek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_bisa_melihat_ringkasan_dashboard_beranda_dari_data_riil(): void
    {
        $pengguna = User::factory()->superadmin()->create([
            'name' => 'Superadmin Sistem',
        ]);

        $produk = ProdukItem::factory()->create([
            'nama' => 'Program Intensif Monitoring',
            'siswa' => 12,
            'omzet' => 12000000,
        ]);

        DataSiswa::factory()->for($produk, 'produk')->create([
            'divalidasi_oleh' => $pengguna->id,
            'nama_lengkap' => 'Bima Valid',
            'status_validasi' => 'validated',
            'status_pembayaran' => 'lunas',
            'jumlah_pembayaran' => 1800000,
            'total_invoice' => 1800000,
            'sisa_tagihan' => 0,
        ]);

        DataSiswa::factory()->for($produk, 'produk')->create([
            'divalidasi_oleh' => $pengguna->id,
            'nama_lengkap' => 'Rani Pending',
            'status_validasi' => 'pending',
            'status_pembayaran' => 'belum_bayar',
            'jumlah_pembayaran' => 0,
            'total_invoice' => 1500000,
            'sisa_tagihan' => 1500000,
        ]);

        Lead::factory()->create([
            'created_by' => $pengguna->id,
            'pic_id' => $pengguna->id,
            'nama_siswa' => 'Lead Follow Up',
            'status' => 'follow_up',
        ]);

        Lead::factory()->create([
            'created_by' => $pengguna->id,
            'pic_id' => $pengguna->id,
            'nama_siswa' => 'Lead Closing',
            'status' => 'closing',
        ]);

        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Monitoring Dasar',
        ]);

        $materi = MateriKursus::factory()->for($kursus, 'kursus')->create([
            'judul' => 'Materi Dasbor Operasional',
        ]);

        $kuis = KuisLms::factory()->for($kursus, 'kursus')->create([
            'judul' => 'Kuis Dasbor Operasional',
        ]);

        ProgresBelajarMateri::factory()->for($pengguna, 'pengguna')->for($materi, 'materiKursus')->create([
            'persen_progres' => 100,
            'selesai_pada' => now(),
        ]);

        HasilKuisPengguna::factory()->for($pengguna, 'pengguna')->for($kuis, 'kuisLms')->create([
            'skor' => 88,
            'jumlah_benar' => 8,
            'jumlah_pertanyaan' => 10,
            'lulus' => true,
        ]);

        Artikel::factory()->for($pengguna, 'penulis')->draft()->create();

        $proyek = Proyek::factory()
            ->for($pengguna, 'penanggungJawab')
            ->for($pengguna, 'pembuat')
            ->create([
                'nama_project' => 'Penyegaran Beranda',
                'status_project' => 'berjalan',
            ]);

        TugasProyek::factory()
            ->for($proyek, 'proyek')
            ->for($pengguna, 'penanggungJawab')
            ->create([
                'judul_tugas' => 'Revisi halaman beranda',
                'status_tugas' => 'berjalan',
                'prioritas_tugas' => 'tinggi',
                'persentase_progres' => 60,
                'tanggal_target' => now()->addDays(2),
            ]);

        NotifikasiPengguna::factory()->for($pengguna, 'pengguna')->create([
            'judul' => 'Review beranda',
            'pesan' => 'Periksa ringkasan dashboard terbaru.',
            'tipe' => 'warning',
        ]);

        LogAktivitas::query()->create([
            'user_id' => $pengguna->id,
            'modul' => 'proyek',
            'aksi' => 'ubah',
            'deskripsi' => 'Memperbarui progres tugas beranda.',
            'subjek_tipe' => 'tugas_proyek',
            'subjek_id' => 1,
            'metadata' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Pusat Kendali Strategis')
            ->assertSee('Mode superadmin')
            ->assertSee('Target Omzet')
            ->assertSee('Rp 12.000.000')
            ->assertSee('1 tervalidasi')
            ->assertSee('Leads Aktif')
            ->assertSee('Project Aktif')
            ->assertSee('Kesehatan Operasional')
            ->assertSee('Ringkasan LMS')
            ->assertSee('Editorial & Link')
            ->assertSee('Jalur Cepat Admin')
            ->assertSee('Aktivitas Terbaru Sistem')
            ->assertSee('Review beranda')
            ->assertSee('Revisi halaman beranda')
            ->assertSee('Memperbarui progres tugas beranda.');
    }

    public function test_level_satu_melihat_mode_pengawasan_di_beranda(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_1)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Pengawasan Operasional')
            ->assertSee('Mode pengawasan')
            ->assertSee('Kesehatan Tim')
            ->assertSee('Funnel & Pengawasan')
            ->assertSee('Jalur Cepat Pengawasan')
            ->assertSee('Pelaporan')
            ->assertSee('Editorial')
            ->assertSee('Prioritas Tugas Pengawasan')
            ->assertDontSee('Mode superadmin');
    }

    public function test_level_dua_melihat_mode_koordinasi_di_beranda(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_2)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Koordinasi Tim')
            ->assertSee('Mode koordinasi')
            ->assertSee('Progres Koordinasi')
            ->assertSee('Sinkronisasi Tim')
            ->assertSee('Jalur Cepat Koordinasi')
            ->assertSee('Penanggung Jawab')
            ->assertSee('Pelaporan')
            ->assertSee('Tindak Lanjut Terdekat')
            ->assertDontSee('Mode personal');
    }

    public function test_level_tiga_hanya_melihat_aktivitas_milik_sendiri_dan_mode_operasional_di_beranda(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $penggunaLain = User::factory()->superadmin()->create();

        LogAktivitas::query()->create([
            'user_id' => $pengguna->id,
            'modul' => 'lead',
            'aksi' => 'lihat',
            'deskripsi' => 'Membuka halaman leads milik sendiri.',
            'subjek_tipe' => 'lead',
            'subjek_id' => 1,
            'metadata' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        LogAktivitas::query()->create([
            'user_id' => $penggunaLain->id,
            'modul' => 'pengguna',
            'aksi' => 'ubah',
            'deskripsi' => 'Mengubah data pengguna lain.',
            'subjek_tipe' => 'pengguna',
            'subjek_id' => 2,
            'metadata' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Operasional')
            ->assertSee('Mode operasional')
            ->assertSee('Notifikasi Baru')
            ->assertSee('Tugas Aktif Saya')
            ->assertSee('Kinerja Operasional')
            ->assertSee('Ringkasan Belajar')
            ->assertSee('Lapangan & Distribusi')
            ->assertSee('Jalur Cepat Operasional')
            ->assertSee('Ads/Iklan')
            ->assertSee('Media Sosial')
            ->assertSee('Aktivitas Terbaru Anda')
            ->assertSee('Tugas Operasional Saya')
            ->assertSee('Membuka halaman leads milik sendiri.')
            ->assertDontSee('Target Omzet')
            ->assertDontSee('Mode personal')
            ->assertDontSee('Mengubah data pengguna lain.');
    }

    public function test_level_empat_melihat_mode_konten_di_beranda(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_4)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Konten & Distribusi')
            ->assertSee('Mode konten')
            ->assertSee('Kesiapan Konten')
            ->assertSee('Pembelajaran & Pengayaan')
            ->assertSee('Distribusi Publik')
            ->assertSee('Jalur Cepat Konten')
            ->assertSee('Artikel Saya')
            ->assertSee('Media Sosial')
            ->assertSee('Youtube')
            ->assertDontSee('Mode operasional');
    }

    public function test_level_lima_melihat_mode_eksekusi_di_beranda(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Eksekusi Harian')
            ->assertSee('Mode eksekusi')
            ->assertSee('Disiplin Harian')
            ->assertSee('Ringkasan Belajar Ringkas')
            ->assertSee('Fokus Personal')
            ->assertSee('Jalur Cepat Eksekusi')
            ->assertSee('Event')
            ->assertSee('Tugas Saya Paling Dekat')
            ->assertDontSee('Mode personal');
    }

    public function test_pemetaan_beranda_level_mengubah_mode_dashboard_sesuai_konfigurasi_admin(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $this
            ->actingAs($superadmin)
            ->put(route('administrasi.pemetaan_beranda.update'), [
                'profil' => [
                    LevelAksesPengguna::LEVEL_1->value => ProfilBerandaPengguna::PENGAWASAN->value,
                    LevelAksesPengguna::LEVEL_2->value => ProfilBerandaPengguna::KOORDINASI->value,
                    LevelAksesPengguna::LEVEL_3->value => ProfilBerandaPengguna::OPERASIONAL->value,
                    LevelAksesPengguna::LEVEL_4->value => ProfilBerandaPengguna::KONTEN->value,
                    LevelAksesPengguna::LEVEL_5->value => ProfilBerandaPengguna::KONTEN->value,
                ],
            ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('dashboard.beranda'));

        $response
            ->assertOk()
            ->assertSee('Konten & Distribusi')
            ->assertSee('Mode konten')
            ->assertSee('Jalur Cepat Konten')
            ->assertDontSee('Mode eksekusi');
    }
}
