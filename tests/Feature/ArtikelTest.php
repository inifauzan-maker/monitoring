<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Models\PresetEditorialPengguna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ArtikelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk_saat_membuka_menu_artikel(): void
    {
        $this->get(route('tools.artikel'))
            ->assertRedirect(route('login'));
    }

    public function test_pengguna_bisa_melihat_halaman_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel'));

        $response
            ->assertOk()
            ->assertSee('Daftar artikel');
    }

    public function test_pengguna_bisa_membuka_dashboard_editorial_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial'));

        $response
            ->assertOk()
            ->assertSee('Dashboard Editorial Artikel');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'lihat',
        ]);
    }

    public function test_dashboard_editorial_bisa_difilter_per_penulis_dan_kategori(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $penulisA = User::factory()->create(['name' => 'Penulis A']);
        $penulisB = User::factory()->create(['name' => 'Penulis B']);
        $kategoriA = KategoriArtikel::factory()->create(['nama' => 'Kategori A', 'slug' => 'kategori-a']);
        $kategoriB = KategoriArtikel::factory()->create(['nama' => 'Kategori B', 'slug' => 'kategori-b']);

        Artikel::factory()->create([
            'judul' => 'Artikel Cocok Filter',
            'slug' => 'artikel-cocok-filter',
            'penulis_id' => $penulisA->id,
            'kategori_artikel_id' => $kategoriA->id,
        ]);

        Artikel::factory()->create([
            'judul' => 'Artikel Penulis Lain',
            'slug' => 'artikel-penulis-lain',
            'penulis_id' => $penulisB->id,
            'kategori_artikel_id' => $kategoriA->id,
        ]);

        Artikel::factory()->create([
            'judul' => 'Artikel Kategori Lain',
            'slug' => 'artikel-kategori-lain',
            'penulis_id' => $penulisA->id,
            'kategori_artikel_id' => $kategoriB->id,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial', [
                'penulis' => $penulisA->id,
                'kategori' => $kategoriA->id,
            ]));

        $response
            ->assertOk()
            ->assertSee('Artikel Cocok Filter')
            ->assertDontSee('Artikel Penulis Lain')
            ->assertDontSee('Artikel Kategori Lain')
            ->assertSee('Penulis: Penulis A')
            ->assertSee('Kategori: Kategori A');
    }

    public function test_dashboard_editorial_menampilkan_notifikasi_editorial(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $penulis = User::factory()->create();

        Artikel::factory()->draft()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Draft Siap Terbit',
            'slug' => 'draft-siap-terbit',
            'kata_kunci_utama' => 'strategi monitoring',
            'judul_seo' => 'Strategi Monitoring Dasar',
            'deskripsi_seo' => 'Strategi monitoring untuk artikel yang sudah siap diterbitkan oleh editor internal.',
            'outline_seo' => '# Strategi Monitoring',
            'sumber_referensi' => ['https://example.com/sumber'],
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => true,
                'metadata_seo_final' => true,
                'referensi_sudah_valid' => true,
                'konten_sudah_dicek' => true,
                'gambar_unggulan_siap' => true,
            ],
        ]);

        Artikel::factory()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Artikel Jadwal Dekat',
            'slug' => 'artikel-jadwal-dekat',
            'diterbitkan_pada' => now()->addDay(),
        ]);

        Artikel::factory()->draft()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Draft Lama Butuh Revisi',
            'slug' => 'draft-lama-butuh-revisi',
            'updated_at' => now()->subDays(10),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial'));

        $response
            ->assertOk()
            ->assertSee('Notifikasi Editorial')
            ->assertSee('Siap diterbitkan')
            ->assertSee('Jadwal terdekat')
            ->assertSee('Perlu revisi')
            ->assertSee('Draft perlu perhatian');
    }

    public function test_dashboard_editorial_bisa_difilter_per_status_editorial(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $penulis = User::factory()->create();

        Artikel::factory()->draft()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Draft Siap Editorial',
            'slug' => 'draft-siap-editorial',
            'kata_kunci_utama' => 'strategi monitoring',
            'judul_seo' => 'Strategi Monitoring Dasar',
            'deskripsi_seo' => 'Strategi monitoring untuk artikel yang sudah siap diterbitkan oleh editor internal.',
            'outline_seo' => '# Strategi Monitoring',
            'sumber_referensi' => ['https://example.com/sumber'],
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => true,
                'metadata_seo_final' => true,
                'referensi_sudah_valid' => true,
                'konten_sudah_dicek' => true,
                'gambar_unggulan_siap' => true,
            ],
        ]);

        Artikel::factory()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Artikel Terbit Editorial',
            'slug' => 'artikel-terbit-editorial',
        ]);

        Artikel::factory()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Artikel Terjadwal Editorial',
            'slug' => 'artikel-terjadwal-editorial',
            'diterbitkan_pada' => now()->addDays(2),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial', [
                'status_editorial' => 'siap_terbit',
            ]));

        $response
            ->assertOk()
            ->assertSee('Draft Siap Editorial')
            ->assertDontSee('Artikel Terbit Editorial')
            ->assertDontSee('Artikel Terjadwal Editorial')
            ->assertSee('Status: Siap Terbit');
    }

    public function test_dashboard_editorial_bisa_difilter_dengan_rentang_tanggal(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $penulis = User::factory()->create();

        Artikel::factory()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Artikel Jadwal April',
            'slug' => 'artikel-jadwal-april',
            'diterbitkan_pada' => now()->setDate(2026, 4, 20)->setTime(9, 0),
        ]);

        Artikel::factory()->create([
            'penulis_id' => $penulis->id,
            'judul' => 'Artikel Jadwal Mei',
            'slug' => 'artikel-jadwal-mei',
            'diterbitkan_pada' => now()->setDate(2026, 5, 5)->setTime(9, 0),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial', [
                'basis_tanggal' => 'jadwal_terbit',
                'tanggal_dari' => '2026-04-01',
                'tanggal_sampai' => '2026-04-30',
            ]));

        $response
            ->assertOk()
            ->assertSee('Artikel Jadwal April')
            ->assertDontSee('Artikel Jadwal Mei')
            ->assertSee('Jadwal Terbit: 01 Apr 2026 s/d 30 Apr 2026');
    }

    public function test_dashboard_editorial_menampilkan_preset_filter_cepat(): void
    {
        Carbon::setTestNow('2026-04-18 08:00:00');

        try {
            $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

            $response = $this
                ->actingAs($pengguna)
                ->get(route('tools.artikel.editorial'));

            $response
                ->assertOk()
                ->assertSee('Preset Cepat')
                ->assertSee('Jadwal Minggu Ini')
                ->assertSee('Draft Lama')
                ->assertSee('Siap Terbit Hari Ini');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pengguna_bisa_menyimpan_preset_editorial_kustom(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kategori = KategoriArtikel::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.editorial.preset.store'), [
                'nama_preset' => 'Filter Penulis Saya',
                'kategori' => $kategori->id,
                'status_editorial' => 'draft',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('preset_editorial_pengguna', [
            'user_id' => $pengguna->id,
            'nama_preset' => 'Filter Penulis Saya',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'tambah',
        ]);
    }

    public function test_dashboard_editorial_hanya_menampilkan_preset_kustom_milik_pengguna_login(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $penggunaLain = User::factory()->create();

        PresetEditorialPengguna::query()->create([
            'user_id' => $pengguna->id,
            'nama_preset' => 'Preset Saya',
            'konfigurasi_filter' => ['status_editorial' => 'draft'],
        ]);

        PresetEditorialPengguna::query()->create([
            'user_id' => $penggunaLain->id,
            'nama_preset' => 'Preset Orang Lain',
            'konfigurasi_filter' => ['status_editorial' => 'terjadwal'],
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial'));

        $response
            ->assertOk()
            ->assertSee('Preset Saya')
            ->assertDontSee('Preset Orang Lain');
    }

    public function test_pengguna_bisa_menghapus_preset_editorial_kustom_miliknya(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $preset = PresetEditorialPengguna::query()->create([
            'user_id' => $pengguna->id,
            'nama_preset' => 'Preset Akan Dihapus',
            'konfigurasi_filter' => ['status_editorial' => 'draft'],
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->delete(route('tools.artikel.editorial.preset.destroy', $preset));

        $response->assertRedirect();

        $this->assertDatabaseMissing('preset_editorial_pengguna', [
            'id' => $preset->id,
        ]);
    }

    public function test_preset_jadwal_minggu_ini_menyaring_dashboard_editorial(): void
    {
        Carbon::setTestNow('2026-04-18 08:00:00');

        try {
            $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
            $penulis = User::factory()->create();

            Artikel::factory()->create([
                'penulis_id' => $penulis->id,
                'judul' => 'Artikel Minggu Ini',
                'slug' => 'artikel-minggu-ini',
                'diterbitkan_pada' => now()->setDate(2026, 4, 19)->setTime(9, 0),
            ]);

            Artikel::factory()->create([
                'penulis_id' => $penulis->id,
                'judul' => 'Artikel Minggu Depan',
                'slug' => 'artikel-minggu-depan',
                'diterbitkan_pada' => now()->setDate(2026, 4, 25)->setTime(9, 0),
            ]);

            $response = $this
                ->actingAs($pengguna)
                ->get(route('tools.artikel.editorial', [
                    'preset_editorial' => 'jadwal_minggu_ini',
                    'status_editorial' => 'terjadwal',
                    'basis_tanggal' => 'jadwal_terbit',
                    'tanggal_dari' => '2026-04-13',
                    'tanggal_sampai' => '2026-04-19',
                ]));

            $response
                ->assertOk()
                ->assertSee('Artikel Minggu Ini')
                ->assertDontSee('Artikel Minggu Depan')
                ->assertSee('Preset: Jadwal Minggu Ini');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pengguna_bisa_menambah_kategori_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.kategori.store'), [
                'nama' => 'Strategi Konten',
                'deskripsi' => 'Kategori untuk strategi dan editorial.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('kategori_artikel', [
            'nama' => 'Strategi Konten',
            'slug' => 'strategi-konten',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'tambah',
        ]);
    }

    public function test_pengguna_bisa_membuat_draft_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kategori = KategoriArtikel::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.store'), [
                'judul' => 'Panduan Editorial Monitoring',
                'kata_kunci_utama' => 'editorial monitoring',
                'ringkasan' => 'Ringkasan awal artikel untuk dashboard monitoring.',
                'konten' => '<p>Isi artikel monitoring.</p>',
                'kategori_artikel_id' => $kategori->id,
                'tingkat_keahlian' => 'menengah',
                'sumber_referensi' => ['https://example.com/referensi'],
                'judul_seo' => 'Panduan Editorial Monitoring',
                'deskripsi_seo' => 'Panduan editorial monitoring untuk kebutuhan konten.',
                'outline_seo' => null,
            ]);

        $artikel = Artikel::query()->first();

        $response->assertRedirect(route('tools.artikel.edit', $artikel));

        $this->assertDatabaseHas('artikel', [
            'judul' => 'Panduan Editorial Monitoring',
            'penulis_id' => $pengguna->id,
            'sudah_diterbitkan' => false,
        ]);

        $this->assertDatabaseHas('artikel_revisi', [
            'artikel_id' => $artikel->id,
            'tipe_pemicu' => 'awal',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'tambah',
            'subjek_id' => $artikel->id,
        ]);
    }

    public function test_hanya_artikel_diterbitkan_yang_muncul_di_daftar(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $artikelTerbit = Artikel::factory()->create([
            'judul' => 'Artikel Terbit Monitoring',
            'slug' => 'artikel-terbit-monitoring',
        ]);

        Artikel::factory()->draft()->create([
            'judul' => 'Artikel Draft Monitoring',
            'slug' => 'artikel-draft-monitoring',
        ]);

        Artikel::factory()->create([
            'judul' => 'Artikel Terjadwal Monitoring',
            'slug' => 'artikel-terjadwal-monitoring',
            'diterbitkan_pada' => now()->addDays(2),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel'));

        $response
            ->assertOk()
            ->assertSee($artikelTerbit->judul)
            ->assertDontSee('Artikel Draft Monitoring')
            ->assertDontSee('Artikel Terjadwal Monitoring');
    }

    public function test_penulis_bisa_preview_draft_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->draft()->create([
            'penulis_id' => $pengguna->id,
            'judul' => 'Draft Preview Monitoring',
            'slug' => 'draft-preview-monitoring',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.preview', $artikel));

        $response
            ->assertOk()
            ->assertSee('Mode preview aktif')
            ->assertSee('Draft Preview Monitoring');
    }

    public function test_pengguna_bisa_membuka_detail_artikel_yang_sudah_diterbitkan(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->create([
            'judul' => 'Strategi Konten Monitoring',
            'slug' => 'strategi-konten-monitoring',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.show', $artikel));

        $response
            ->assertOk()
            ->assertSee('Strategi Konten Monitoring');
    }

    public function test_artikel_terjadwal_tidak_bisa_dibuka_dari_halaman_publikasi(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->create([
            'judul' => 'Strategi Terjadwal Monitoring',
            'slug' => 'strategi-terjadwal-monitoring',
            'diterbitkan_pada' => now()->addDay(),
        ]);

        $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.show', $artikel))
            ->assertNotFound();
    }

    public function test_penulis_bisa_menerbitkan_artikel_yang_sudah_lengkap(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->draft()->create([
            'penulis_id' => $pengguna->id,
            'kata_kunci_utama' => 'strategi monitoring',
            'judul_seo' => 'Strategi Monitoring',
            'deskripsi_seo' => 'Strategi monitoring untuk artikel yang siap terbit.',
            'outline_seo' => '# Strategi Monitoring',
            'sumber_referensi' => ['https://example.com/sumber'],
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => true,
                'metadata_seo_final' => true,
                'referensi_sudah_valid' => true,
                'konten_sudah_dicek' => true,
                'gambar_unggulan_siap' => true,
            ],
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.terbitkan', $artikel));

        $response->assertRedirect(route('tools.artikel.edit', $artikel));

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'sudah_diterbitkan' => true,
        ]);

        $this->assertDatabaseHas('artikel_revisi', [
            'artikel_id' => $artikel->id,
            'tipe_pemicu' => 'diterbitkan',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'ubah',
            'subjek_id' => $artikel->id,
        ]);
    }

    public function test_penulis_bisa_menjadwalkan_terbit_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->draft()->create([
            'penulis_id' => $pengguna->id,
            'kata_kunci_utama' => 'strategi monitoring',
            'judul_seo' => 'Strategi Monitoring Dasar',
            'deskripsi_seo' => 'Strategi monitoring untuk artikel yang dijadwalkan terbit secara otomatis nanti.',
            'outline_seo' => '# Strategi Monitoring',
            'sumber_referensi' => ['https://example.com/sumber'],
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => true,
                'metadata_seo_final' => true,
                'referensi_sudah_valid' => true,
                'konten_sudah_dicek' => true,
                'gambar_unggulan_siap' => true,
            ],
            'diterbitkan_pada' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.terbitkan', $artikel));

        $response->assertRedirect(route('tools.artikel.edit', $artikel));

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'sudah_diterbitkan' => true,
        ]);

        $this->assertDatabaseHas('artikel_revisi', [
            'artikel_id' => $artikel->id,
            'tipe_pemicu' => 'dijadwalkan',
        ]);
    }

    public function test_penulis_tidak_bisa_menerbitkan_artikel_jika_checklist_belum_lengkap(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->draft()->create([
            'penulis_id' => $pengguna->id,
            'kata_kunci_utama' => 'strategi monitoring',
            'judul_seo' => 'Strategi Monitoring',
            'deskripsi_seo' => 'Strategi monitoring untuk artikel yang belum siap terbit.',
            'outline_seo' => '# Strategi Monitoring',
            'sumber_referensi' => ['https://example.com/sumber'],
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => true,
                'metadata_seo_final' => true,
                'referensi_sudah_valid' => false,
                'konten_sudah_dicek' => true,
                'gambar_unggulan_siap' => false,
            ],
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->from(route('tools.artikel.edit', $artikel))
            ->post(route('tools.artikel.terbitkan', $artikel));

        $response
            ->assertRedirect(route('tools.artikel.edit', $artikel))
            ->assertSessionHasErrors('publikasi');

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'sudah_diterbitkan' => false,
        ]);
    }

    public function test_penulis_bisa_unpublish_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->create([
            'penulis_id' => $pengguna->id,
            'judul' => 'Artikel Aktif Monitoring',
            'slug' => 'artikel-aktif-monitoring',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.batalkan_terbit', $artikel));

        $response->assertRedirect(route('tools.artikel.edit', $artikel));

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'sudah_diterbitkan' => false,
            'diterbitkan_pada' => null,
        ]);

        $this->assertDatabaseHas('artikel_revisi', [
            'artikel_id' => $artikel->id,
            'tipe_pemicu' => 'dibatalkan_terbit',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'ubah',
            'subjek_id' => $artikel->id,
        ]);
    }

    public function test_penulis_bisa_memulihkan_revisi_artikel(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->create([
            'penulis_id' => $pengguna->id,
            'judul' => 'Versi Baru Monitoring',
            'slug' => 'versi-baru-monitoring',
        ]);

        $revisi = $artikel->revisi()->create([
            'penulis_id' => $pengguna->id,
            'tipe_pemicu' => 'manual',
            'snapshot' => [
                'judul' => 'Versi Lama Monitoring',
                'slug' => 'versi-lama-monitoring',
                'kata_kunci_utama' => 'monitoring lama',
                'ringkasan' => 'Ringkasan versi lama.',
                'konten' => '<p>Konten versi lama.</p>',
                'kategori_artikel_id' => $artikel->kategori_artikel_id,
                'tingkat_keahlian' => 'menengah',
                'bio_penulis' => 'Bio lama.',
                'sumber_referensi' => ['https://example.com/lama'],
                'judul_seo' => 'Versi Lama Monitoring',
                'deskripsi_seo' => 'Deskripsi versi lama.',
                'outline_seo' => '# Versi Lama Monitoring',
                'checklist_kesiapan' => [
                    'keyword_sudah_dicek' => true,
                    'metadata_seo_final' => true,
                    'referensi_sudah_valid' => true,
                    'konten_sudah_dicek' => true,
                    'gambar_unggulan_siap' => true,
                ],
                'alt_gambar_unggulan' => null,
            ],
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('tools.artikel.revisi.pulihkan', [$artikel, $revisi]));

        $response->assertRedirect(route('tools.artikel.edit', $artikel->fresh()));

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'judul' => 'Versi Lama Monitoring',
            'slug' => 'versi-lama-monitoring',
        ]);
    }

    public function test_penulis_bisa_export_pdf_artikel_preview(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $artikel = Artikel::factory()->draft()->create([
            'penulis_id' => $pengguna->id,
            'judul' => 'Draft PDF Monitoring',
            'slug' => 'draft-pdf-monitoring',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.pdf', $artikel));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'ekspor',
            'subjek_id' => $artikel->id,
        ]);
    }

    public function test_pengguna_bisa_export_pdf_dashboard_editorial(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.artikel.editorial.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'artikel',
            'aksi' => 'ekspor',
        ]);
    }
}
