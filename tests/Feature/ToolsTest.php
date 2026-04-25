<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\AktivitasLinkPublik;
use App\Models\LinkPengguna;
use App\Models\StatistikLinkHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_membuka_halaman_link(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('tools.link'));

        $response
            ->assertOk()
            ->assertSee('Link')
            ->assertSee('Tambah Link')
            ->assertSee('Daftar Link')
            ->assertSee('Export CSV')
            ->assertSee('Export PDF');
    }

    public function test_pengguna_bisa_memperbarui_profil_link_publik_dan_domain_kustom(): void
    {
        Storage::fake('public');

        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'slug_link' => 'profil-awal-1',
        ]);

        $this->actingAs($pengguna)
            ->post(route('tools.link.profil.update'), [
                '_method' => 'PUT',
                'slug_link' => 'profil-baru',
                'nama_tampil_link' => 'Superadmin Publik',
                'nomor_wa_link' => '0812-3456-7890',
                'domain_kustom_link' => 'Link.Example.Test',
                'judul_link' => 'Link Publik Monitoring',
                'headline_link' => 'Satu pintu untuk semua tautan penting',
                'bio_link' => 'Semua tautan penting ada di sini.',
                'label_cta_link' => 'Hubungi Tim',
                'url_cta_link' => 'example.com/kontak',
                'tema_link' => 'mint',
                'avatar_link' => UploadedFile::fake()->image('avatar-link.png', 400, 400),
            ])
            ->assertRedirect(route('tools.link'));

        $this->assertDatabaseHas('users', [
            'id' => $pengguna->id,
            'slug_link' => 'profil-baru',
            'nama_tampil_link' => 'Superadmin Publik',
            'nomor_wa_link' => '6281234567890',
            'domain_kustom_link' => 'link.example.test',
            'judul_link' => 'Link Publik Monitoring',
            'headline_link' => 'Satu pintu untuk semua tautan penting',
            'bio_link' => 'Semua tautan penting ada di sini.',
            'label_cta_link' => 'Hubungi Tim',
            'url_cta_link' => 'https://example.com/kontak',
            'tema_link' => 'mint',
        ]);

        $pengguna->refresh();

        $this->assertNotNull($pengguna->avatar_link);
        Storage::disk('public')->assertExists($pengguna->avatar_link);
    }

    public function test_pengguna_bisa_menambah_link_dengan_url_dinormalisasi(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $this->actingAs($pengguna)
            ->post(route('tools.link.store'), [
                'judul' => 'Katalog Program',
                'deskripsi' => 'Informasi program terbaru',
                'url' => 'example.com/program',
                'urutan' => 3,
            ])
            ->assertRedirect(route('tools.link'));

        $this->assertDatabaseHas('link_pengguna', [
            'user_id' => $pengguna->id,
            'judul' => 'Katalog Program',
            'url' => 'https://example.com/program',
            'urutan' => 3,
            'aktif' => true,
        ]);

        $idLink = LinkPengguna::query()->where('judul', 'Katalog Program')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'link',
            'aksi' => 'tambah',
            'subjek_id' => $idLink,
        ]);
    }

    public function test_pengguna_bisa_memperbarui_link_miliknya(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $link = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Lama',
            'deskripsi' => 'Deskripsi lama',
            'url' => 'https://lama.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->actingAs($pengguna)
            ->put(route('tools.link.update', $link), [
                'judul' => 'Link Baru',
                'deskripsi' => 'Deskripsi baru',
                'url' => 'baru.test',
                'urutan' => 9,
            ])
            ->assertRedirect(route('tools.link').'#editor-link-'.$link->id);

        $this->assertDatabaseHas('link_pengguna', [
            'id' => $link->id,
            'judul' => 'Link Baru',
            'deskripsi' => 'Deskripsi baru',
            'url' => 'https://baru.test',
            'urutan' => 9,
            'aktif' => false,
        ]);
    }

    public function test_pengguna_bisa_menghapus_link_miliknya(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $link = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Hapus',
            'deskripsi' => null,
            'url' => 'https://hapus.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->actingAs($pengguna)
            ->delete(route('tools.link.destroy', $link))
            ->assertRedirect(route('tools.link'));

        $this->assertDatabaseMissing('link_pengguna', [
            'id' => $link->id,
        ]);
    }

    public function test_pengguna_tidak_bisa_mengelola_link_milik_pengguna_lain(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $pemilikLain = User::factory()->create();
        $link = LinkPengguna::create([
            'user_id' => $pemilikLain->id,
            'judul' => 'Link Privat',
            'deskripsi' => null,
            'url' => 'https://privat.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->actingAs($pengguna)
            ->put(route('tools.link.update', $link), [
                'judul' => 'Tidak Boleh',
                'url' => 'tidak-boleh.test',
            ])
            ->assertNotFound();

        $this->actingAs($pengguna)
            ->delete(route('tools.link.destroy', $link))
            ->assertNotFound();
    }

    public function test_url_link_harus_valid(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $this->actingAs($pengguna)
            ->from(route('tools.link'))
            ->post(route('tools.link.store'), [
                'judul' => 'Link Tidak Valid',
                'url' => 'abc',
            ])
            ->assertRedirect(route('tools.link'))
            ->assertSessionHasErrors('url');
    }

    public function test_halaman_link_publik_hanya_menampilkan_link_aktif(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'publik-link-1',
            'judul_link' => 'Halaman Link Publik',
        ]);

        LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Aktif',
            'deskripsi' => 'Tampil di publik',
            'url' => 'https://aktif.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Nonaktif',
            'deskripsi' => 'Tidak tampil',
            'url' => 'https://nonaktif.test',
            'urutan' => 2,
            'aktif' => false,
        ]);

        $this->get(route('publik.link.show', $pengguna))
            ->assertOk()
            ->assertSee('Halaman Link Publik')
            ->assertSee('Link Aktif')
            ->assertDontSee('Link Nonaktif');

        $this->assertDatabaseHas('statistik_link_harian', [
            'user_id' => $pengguna->id,
            'jenis_aktivitas' => StatistikLinkHarian::KUNJUNGAN_HALAMAN,
            'total' => 1,
        ]);
        $this->assertDatabaseHas('aktivitas_link_publik', [
            'user_id' => $pengguna->id,
            'jenis_aktivitas' => StatistikLinkHarian::KUNJUNGAN_HALAMAN,
        ]);
    }

    public function test_halaman_link_publik_menampilkan_nama_kustom_dan_avatar(): void
    {
        Storage::fake('public');

        $avatarPath = UploadedFile::fake()
            ->image('avatar-publik.png', 480, 480)
            ->store('avatar-link', 'public');

        $pengguna = User::factory()->create([
            'slug_link' => 'avatar-link-1',
            'nama_tampil_link' => 'Nama Publik Admin',
            'nomor_wa_link' => '6281234567890',
            'judul_link' => 'Brand Monitoring',
            'avatar_link' => $avatarPath,
        ]);

        $this->get(route('publik.link.show', $pengguna))
            ->assertOk()
            ->assertSee('Nama Publik Admin')
            ->assertSee('Brand Monitoring')
            ->assertSee('6281 2345 6789')
            ->assertSee('Chat WhatsApp')
            ->assertSee(Storage::disk('public')->url($avatarPath), false);
    }

    public function test_halaman_link_publik_memakai_fallback_inisial_jika_avatar_tidak_tersedia(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'avatar-fallback-1',
            'nama_tampil_link' => 'Bimbel Vilmer',
            'avatar_link' => 'avatar-link/tidak-ada.png',
        ]);

        $this->get(route('publik.link.show', $pengguna))
            ->assertOk()
            ->assertSee('BV')
            ->assertDontSee('avatar-link/tidak-ada.png');
    }

    public function test_buka_link_publik_menambah_total_klik_dan_redirect_ke_url_tujuan(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'klik-link-1',
        ]);

        $link = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Buka Katalog',
            'deskripsi' => null,
            'url' => 'example.com/katalog',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->get(route('publik.link.buka', [$pengguna, $link]))
            ->assertRedirect('https://example.com/katalog');

        $this->assertDatabaseHas('link_pengguna', [
            'id' => $link->id,
            'total_klik' => 1,
        ]);

        $this->assertDatabaseHas('statistik_link_harian', [
            'user_id' => $pengguna->id,
            'link_pengguna_id' => $link->id,
            'jenis_aktivitas' => StatistikLinkHarian::KLIK_LINK,
            'total' => 1,
        ]);
        $this->assertDatabaseHas('aktivitas_link_publik', [
            'user_id' => $pengguna->id,
            'link_pengguna_id' => $link->id,
            'jenis_aktivitas' => StatistikLinkHarian::KLIK_LINK,
            'url_tujuan' => 'https://example.com/katalog',
        ]);
    }

    public function test_buka_link_publik_gagal_jika_link_tidak_aktif_atau_bukan_milik_pengguna(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'pemilik-link-1',
        ]);
        $penggunaLain = User::factory()->create([
            'slug_link' => 'pemilik-link-2',
        ]);

        $linkNonaktif = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Mati',
            'deskripsi' => null,
            'url' => 'https://mati.test',
            'urutan' => 1,
            'aktif' => false,
        ]);

        $linkPenggunaLain = LinkPengguna::create([
            'user_id' => $penggunaLain->id,
            'judul' => 'Link Orang Lain',
            'deskripsi' => null,
            'url' => 'https://lain.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->get(route('publik.link.buka', [$pengguna, $linkNonaktif]))
            ->assertNotFound();

        $this->get(route('publik.link.buka', [$pengguna, $linkPenggunaLain]))
            ->assertNotFound();
    }

    public function test_buka_cta_publik_mencatat_statistik_dan_redirect(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'cta-link-1',
            'url_cta_link' => 'https://example.com/cta',
            'label_cta_link' => 'Hubungi Sekarang',
        ]);

        $this->get(route('publik.link.cta', $pengguna))
            ->assertRedirect('https://example.com/cta');

        $this->assertDatabaseHas('statistik_link_harian', [
            'user_id' => $pengguna->id,
            'jenis_aktivitas' => StatistikLinkHarian::KLIK_CTA,
            'total' => 1,
        ]);
        $this->assertDatabaseHas('aktivitas_link_publik', [
            'user_id' => $pengguna->id,
            'jenis_aktivitas' => StatistikLinkHarian::KLIK_CTA,
            'url_tujuan' => 'https://example.com/cta',
        ]);
    }

    public function test_halaman_link_publik_menampilkan_headline_cta_dan_tema_yang_dipilih(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'tema-link-1',
            'judul_link' => 'Halaman Link Tema',
            'headline_link' => 'Promo dan katalog dalam satu halaman',
            'bio_link' => 'Silakan pilih tautan yang ingin dibuka.',
            'label_cta_link' => 'Lihat Promo Utama',
            'url_cta_link' => 'https://example.com/promo',
            'tema_link' => 'night',
        ]);

        $this->get(route('publik.link.show', $pengguna))
            ->assertOk()
            ->assertSee('Halaman Link Tema')
            ->assertSee('Promo dan katalog dalam satu halaman')
            ->assertSee('Lihat Promo Utama')
            ->assertDontSee('Night Studio');
    }

    public function test_dashboard_link_menampilkan_statistik_harian_top_link_dan_source_traffic(): void
    {
        Carbon::setTestNow('2026-04-18 10:00:00');

        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'slug_link' => 'analitik-link-1',
        ]);

        $linkA = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link A',
            'deskripsi' => null,
            'url' => 'https://a.test',
            'urutan' => 1,
            'aktif' => true,
            'total_klik' => 5,
        ]);

        $linkB = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link B',
            'deskripsi' => null,
            'url' => 'https://b.test',
            'urutan' => 2,
            'aktif' => true,
            'total_klik' => 2,
        ]);

        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, null, 'facebook.com', '2026-04-18 08:00:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, null, 'google.com', '2026-04-17 08:00:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KLIK_CTA, null, 'facebook.com', '2026-04-18 08:10:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KLIK_LINK, $linkA, 'facebook.com', '2026-04-18 08:15:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KLIK_LINK, $linkB, 'google.com', '2026-04-17 09:00:00');

        $this->actingAs($pengguna)
            ->get(route('tools.link', ['rentang' => 7]))
            ->assertOk()
            ->assertSee('Statistik Link Harian')
            ->assertSee('Traffic Source')
            ->assertSee('Top Link')
            ->assertSee('Link A')
            ->assertSee('Link B')
            ->assertSee('facebook.com')
            ->assertSee('google.com')
            ->assertSee('Kunjungan 7 Hari')
            ->assertSee('Klik CTA 7 Hari');

        Carbon::setTestNow();
    }

    public function test_dashboard_link_bisa_filter_source_traffic(): void
    {
        Carbon::setTestNow('2026-04-18 10:00:00');

        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'slug_link' => 'filter-link-1',
        ]);

        $linkA = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Facebook',
            'deskripsi' => null,
            'url' => 'https://facebook-link.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $linkB = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Google',
            'deskripsi' => null,
            'url' => 'https://google-link.test',
            'urutan' => 2,
            'aktif' => false,
        ]);

        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, null, 'facebook.com', '2026-04-18 08:00:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, null, 'google.com', '2026-04-18 09:00:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KLIK_LINK, $linkA, 'facebook.com', '2026-04-18 08:15:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KLIK_LINK, $linkB, 'google.com', '2026-04-18 09:15:00');

        $response = $this->actingAs($pengguna)
            ->get(route('tools.link', ['rentang' => 7, 'source' => 'facebook.com']));

        $response
            ->assertOk()
            ->assertSee('Source: facebook.com')
            ->assertViewHas('analitik', function (array $analitik): bool {
                return $analitik['source_filter'] === 'facebook.com'
                    && $analitik['metrik']['kunjungan_halaman'] === 1
                    && $analitik['metrik']['klik_link'] === 1
                    && $analitik['top_link']->count() === 1
                    && $analitik['top_link']->first()['link']->judul === 'Link Facebook';
            });

        Carbon::setTestNow();
    }

    public function test_pengguna_bisa_export_analitik_link_ke_csv(): void
    {
        Carbon::setTestNow('2026-04-18 10:00:00');

        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'slug_link' => 'export-link-1',
        ]);

        $link = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Link Export',
            'deskripsi' => null,
            'url' => 'https://export.test',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, null, 'facebook.com', '2026-04-18 08:00:00');
        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KLIK_LINK, $link, 'facebook.com', '2026-04-18 08:15:00');

        $response = $this->actingAs($pengguna)
            ->get(route('tools.link.analitik.export', [
                'rentang' => 7,
                'source' => 'facebook.com',
                'format' => 'csv',
            ]));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('Ringkasan Analitik Link', $response->streamedContent());
        $this->assertStringContainsString('facebook.com', $response->streamedContent());

        Carbon::setTestNow();
    }

    public function test_pengguna_bisa_export_analitik_link_ke_pdf(): void
    {
        Carbon::setTestNow('2026-04-18 10:00:00');

        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'slug_link' => 'export-pdf-1',
        ]);

        $this->buatAktivitasLink($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, null, 'newsletter', '2026-04-18 08:00:00');

        $response = $this->actingAs($pengguna)
            ->get(route('tools.link.analitik.export', [
                'rentang' => 7,
                'format' => 'pdf',
            ]));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());

        Carbon::setTestNow();
    }

    public function test_domain_kustom_menampilkan_halaman_publik_di_root(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'domain-kustom-1',
            'judul_link' => 'Landing Domain Kustom',
            'domain_kustom_link' => 'link.example.test',
        ]);

        LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Program Unggulan',
            'deskripsi' => 'Tautan utama domain kustom',
            'url' => 'https://example.com/program',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->get('http://link.example.test/')
            ->assertOk()
            ->assertSee('Landing Domain Kustom')
            ->assertSee('Domain Kustom')
            ->assertSee('Program Unggulan');

        $this->assertNotNull($pengguna->fresh()->domain_kustom_terhubung_pada);
    }

    public function test_domain_kustom_buka_link_mencatat_source_traffic(): void
    {
        $pengguna = User::factory()->create([
            'slug_link' => 'domain-kustom-klik-1',
            'domain_kustom_link' => 'klik.example.test',
        ]);

        $link = LinkPengguna::create([
            'user_id' => $pengguna->id,
            'judul' => 'Promo Domain Kustom',
            'deskripsi' => null,
            'url' => 'https://example.com/promo',
            'urutan' => 1,
            'aktif' => true,
        ]);

        $this->get('http://klik.example.test/link/'.$link->id.'?source=instagram.com')
            ->assertRedirect('https://example.com/promo');

        $this->assertDatabaseHas('aktivitas_link_publik', [
            'user_id' => $pengguna->id,
            'link_pengguna_id' => $link->id,
            'jenis_aktivitas' => StatistikLinkHarian::KLIK_LINK,
            'sumber_traffic' => 'instagram.com',
        ]);
    }

    private function buatAktivitasLink(
        User $pengguna,
        string $jenisAktivitas,
        ?LinkPengguna $linkPengguna = null,
        ?string $sumberTraffic = null,
        ?string $waktu = null,
    ): void {
        $timestamp = $waktu ? Carbon::parse($waktu) : now();

        AktivitasLinkPublik::create([
            'user_id' => $pengguna->id,
            'link_pengguna_id' => $linkPengguna?->id,
            'jenis_aktivitas' => $jenisAktivitas,
            'session_id' => 'sesi-'.Str::random(8),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Test',
            'referrer' => $sumberTraffic ? 'https://'.$sumberTraffic.'/referer' : null,
            'sumber_traffic' => $sumberTraffic ?? 'direct / unknown',
            'url_tujuan' => $linkPengguna?->urlTujuan(),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
