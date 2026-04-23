<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\BankSoalLms;
use App\Models\KuisLms;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\PercobaanKuisPengguna;
use App\Models\PertanyaanKuis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuisLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_kuis_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create(['judul' => 'Kursus Kuis Monitoring']);
        KuisLms::factory()->create([
            'judul' => 'Kuis Dasar Monitoring',
            'kursus_id' => $kursus->id,
            'target_tipe' => 'kursus',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis'));

        $response
            ->assertOk()
            ->assertSee('Kuis')
            ->assertSee('Kuis Dasar Monitoring')
            ->assertSee('Kursus Kuis Monitoring');
    }

    public function test_pengguna_non_superadmin_tidak_bisa_menambah_kuis_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $kursus = Kursus::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.store'), [
                'judul' => 'Kuis Dilarang',
                'target_tipe' => 'kursus',
                'kursus_id' => $kursus->id,
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_kuis_kursus_lms(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create([
            'judul' => 'Kursus Operasional',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.store'), [
                'judul' => 'Kuis Kursus Operasional',
                'target_tipe' => 'kursus',
                'kursus_id' => $kursus->id,
                'nilai_lulus' => 80,
                'durasi_menit' => 30,
                'maksimal_percobaan' => 2,
                'aktif' => 1,
            ]);

        $response->assertRedirect(route('lms.kuis'));

        $this->assertDatabaseHas('kuis_lms', [
            'judul' => 'Kuis Kursus Operasional',
            'target_tipe' => 'kursus',
            'kursus_id' => $kursus->id,
            'nilai_lulus' => 80,
            'durasi_menit' => 30,
            'maksimal_percobaan' => 2,
        ]);

        $idKuis = KuisLms::query()->where('judul', 'Kuis Kursus Operasional')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'kuis',
            'aksi' => 'tambah',
            'subjek_id' => $idKuis,
        ]);
    }

    public function test_superadmin_bisa_menambah_pertanyaan_kuis_lms(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $kuis = KuisLms::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.pertanyaan.store', $kuis), [
                'pertanyaan' => 'Apa warna badge sukses?',
                'opsi_1' => 'Merah',
                'opsi_2' => 'Hijau',
                'opsi_3' => 'Kuning',
                'opsi_4' => 'Biru',
                'indeks_jawaban_benar' => 1,
                'urutan' => 1,
            ]);

        $response->assertRedirect(route('lms.kuis.show', $kuis));

        $this->assertDatabaseHas('pertanyaan_kuis', [
            'kuis_lms_id' => $kuis->id,
            'pertanyaan' => 'Apa warna badge sukses?',
            'indeks_jawaban_benar' => 1,
        ]);
    }

    public function test_pengguna_bisa_mengerjakan_kuis_lms_dan_skor_dihitung(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'nilai_lulus' => 60,
            'aktif' => true,
        ]);

        $pertanyaanA = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 0,
            'urutan' => 1,
        ]);

        $pertanyaanB = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 2,
            'urutan' => 2,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaanA->id => 0,
                    'pertanyaan_'.$pertanyaanB->id => 1,
                ],
            ]);

        $response->assertRedirect(route('lms.kuis.show', $kuis));

        $this->assertDatabaseHas('hasil_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'jumlah_benar' => 1,
            'jumlah_pertanyaan' => 2,
            'skor' => 50,
            'lulus' => false,
            'jumlah_percobaan' => 1,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'kuis',
            'aksi' => 'submit',
        ]);
    }

    public function test_halaman_detail_kuis_lms_menampilkan_timer_aktif(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'durasi_menit' => 15,
            'aktif' => true,
        ]);

        PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'urutan' => 1,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis));

        $response
            ->assertOk()
            ->assertSee('Timer Kuis Aktif')
            ->assertSee('15 menit');

        $this->assertNotNull(session('lms.kuis.timer.'.$pengguna->id.'.'.$kuis->id));
    }

    public function test_submit_manual_kuis_timer_tetap_memerlukan_jawaban_lengkap(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'durasi_menit' => 10,
            'aktif' => true,
        ]);

        $pertanyaanA = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'urutan' => 1,
        ]);

        $pertanyaanB = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'urutan' => 2,
        ]);

        $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis))
            ->assertOk();

        $response = $this
            ->actingAs($pengguna)
            ->from(route('lms.kuis.show', $kuis))
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaanA->id => 0,
                ],
            ]);

        $response
            ->assertRedirect(route('lms.kuis.show', $kuis))
            ->assertSessionHasErrors([
                'jawaban.pertanyaan_'.$pertanyaanB->id,
            ]);
    }

    public function test_submit_otomatis_saat_timer_habis_tetap_menyimpan_hasil_kuis(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'nilai_lulus' => 60,
            'durasi_menit' => 1,
            'aktif' => true,
        ]);

        PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 0,
            'urutan' => 1,
        ]);

        PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 1,
            'urutan' => 2,
        ]);

        $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis))
            ->assertOk();

        $paketKey = 'lms.kuis.paket.'.$pengguna->id.'.'.$kuis->id;
        $timerKey = 'lms.kuis.timer.'.$pengguna->id.'.'.$kuis->id;
        $paket = session($paketKey);
        $timer = session($timerKey);

        $this->assertIsArray($paket);
        $this->assertIsArray($timer);

        $timer['berakhir_pada'] = now()->subSecond()->toIso8601String();

        $response = $this
            ->withSession([
                $paketKey => $paket,
                $timerKey => $timer,
            ])
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'submit_otomatis' => 1,
                'jawaban' => [
                    $paket[0]['kode'] => $paket[0]['indeks_jawaban_benar_tampil'],
                ],
            ]);

        $response
            ->assertRedirect(route('lms.kuis.show', $kuis))
            ->assertSessionHas('status', 'Waktu kuis habis. Jawaban yang sudah masuk disimpan, tetapi nilai belum melewati batas lulus.');

        $this->assertDatabaseHas('hasil_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'jumlah_benar' => 1,
            'jumlah_pertanyaan' => 2,
            'skor' => 50,
            'lulus' => false,
            'jumlah_percobaan' => 1,
        ]);
    }

    public function test_pengguna_tidak_bisa_melebihi_batas_maksimal_percobaan_kuis(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'maksimal_percobaan' => 1,
            'aktif' => true,
        ]);

        $pertanyaan = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 1,
            'urutan' => 1,
        ]);

        $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaan->id => 1,
                ],
            ])
            ->assertRedirect(route('lms.kuis.show', $kuis));

        $response = $this
            ->actingAs($pengguna)
            ->from(route('lms.kuis.show', $kuis))
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaan->id => 1,
                ],
            ]);

        $response
            ->assertRedirect(route('lms.kuis.show', $kuis))
            ->assertSessionHasErrors([
                'kuis',
            ]);

        $this->assertDatabaseMissing('percobaan_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'percobaan_ke' => 2,
        ]);
    }

    public function test_halaman_detail_kuis_menampilkan_pesan_batas_percobaan_saat_sudah_tercapai(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'maksimal_percobaan' => 1,
            'aktif' => true,
        ]);

        $pertanyaan = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 0,
            'urutan' => 1,
        ]);

        $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaan->id => 0,
                ],
            ])
            ->assertRedirect(route('lms.kuis.show', $kuis));

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis));

        $response
            ->assertOk()
            ->assertSee('Batas percobaan sudah tercapai')
            ->assertSee('1 dari 1 percobaan');
    }

    public function test_halaman_materi_menampilkan_kuis_terkait(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create(['judul' => 'Kursus Kuis Materi']);
        $materi = MateriKursus::factory()->create([
            'kursus_id' => $kursus->id,
            'judul' => 'Materi dengan Kuis',
        ]);

        KuisLms::factory()->create([
            'judul' => 'Kuis Materi',
            'target_tipe' => 'materi',
            'kursus_id' => $kursus->id,
            'materi_kursus_id' => $materi->id,
        ]);

        KuisLms::factory()->create([
            'judul' => 'Kuis Kursus',
            'target_tipe' => 'kursus',
            'kursus_id' => $kursus->id,
            'materi_kursus_id' => null,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.materi.show', $materi));

        $response
            ->assertOk()
            ->assertSee('Kuis Terkait')
            ->assertSee('Kuis Materi')
            ->assertSee('Kuis Kursus');
    }

    public function test_superadmin_bisa_menempelkan_bank_soal_ke_kuis_lms(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create();
        $kuis = KuisLms::factory()->create([
            'kursus_id' => $kursus->id,
            'gunakan_bank_soal' => true,
        ]);
        $bankSoal = BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'aktif' => true,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.bank_soal.store', $kuis), [
                'bank_soal_lms_id' => $bankSoal->id,
                'urutan' => 3,
            ]);

        $response->assertRedirect(route('lms.kuis.show', $kuis));

        $this->assertDatabaseHas('bank_soal_kuis_lms', [
            'kuis_lms_id' => $kuis->id,
            'bank_soal_lms_id' => $bankSoal->id,
            'urutan' => 3,
        ]);
    }

    public function test_kuis_lms_bisa_mengacak_soal_dari_bank_soal(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create();
        $kuis = KuisLms::factory()->create([
            'kursus_id' => $kursus->id,
            'gunakan_bank_soal' => true,
            'acak_soal' => true,
            'acak_opsi_jawaban' => true,
            'jumlah_soal_tampil' => 2,
            'nilai_lulus' => 50,
            'aktif' => true,
        ]);

        $bankSoalA = BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'indeks_jawaban_benar' => 0,
            'aktif' => true,
        ]);
        $bankSoalB = BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'indeks_jawaban_benar' => 1,
            'aktif' => true,
        ]);
        $bankSoalC = BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'indeks_jawaban_benar' => 2,
            'aktif' => true,
        ]);

        $kuis->bankSoal()->attach([
            $bankSoalA->id => ['urutan' => 1],
            $bankSoalB->id => ['urutan' => 2],
            $bankSoalC->id => ['urutan' => 3],
        ]);

        $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis))
            ->assertOk();

        $paket = session('lms.kuis.paket.'.$pengguna->id.'.'.$kuis->id);

        $this->assertIsArray($paket);
        $this->assertCount(2, $paket);

        $jawaban = [];

        foreach ($paket as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('kode', $item);
            $this->assertArrayHasKey('indeks_jawaban_benar_tampil', $item);

            $jawaban[$item['kode']] = $item['indeks_jawaban_benar_tampil'];
        }

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => $jawaban,
            ]);

        $response->assertRedirect(route('lms.kuis.show', $kuis));

        $this->assertDatabaseHas('hasil_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'jumlah_benar' => 2,
            'jumlah_pertanyaan' => 2,
            'skor' => 100,
            'lulus' => true,
        ]);

        $this->assertDatabaseHas('percobaan_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'percobaan_ke' => 1,
            'jumlah_benar' => 2,
            'jumlah_pertanyaan' => 2,
            'skor' => 100,
            'lulus' => true,
        ]);
    }

    public function test_submit_kuis_lms_menyimpan_riwayat_percobaan(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'nilai_lulus' => 50,
            'aktif' => true,
        ]);

        $pertanyaan = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'indeks_jawaban_benar' => 2,
            'urutan' => 1,
        ]);

        $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaan->id => 0,
                ],
            ])
            ->assertRedirect(route('lms.kuis.show', $kuis));

        $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaan->id => 2,
                ],
            ])
            ->assertRedirect(route('lms.kuis.show', $kuis));

        $this->assertDatabaseHas('hasil_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'jumlah_percobaan' => 2,
            'skor' => 100,
            'lulus' => true,
        ]);

        $this->assertDatabaseHas('percobaan_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'percobaan_ke' => 1,
            'skor' => 0,
            'lulus' => false,
        ]);

        $this->assertDatabaseHas('percobaan_kuis_pengguna', [
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'percobaan_ke' => 2,
            'skor' => 100,
            'lulus' => true,
        ]);
    }

    public function test_halaman_detail_kuis_menampilkan_review_percobaan_terakhir(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kuis = KuisLms::factory()->create([
            'nilai_lulus' => 50,
            'aktif' => true,
        ]);

        $pertanyaan = PertanyaanKuis::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'pertanyaan' => 'Apa warna badge sukses?',
            'opsi_jawaban' => ['Merah', 'Hijau', 'Biru', 'Kuning'],
            'indeks_jawaban_benar' => 1,
            'urutan' => 1,
        ]);

        $this
            ->actingAs($pengguna)
            ->post(route('lms.kuis.submit', $kuis), [
                'jawaban' => [
                    'pertanyaan_'.$pertanyaan->id => 0,
                ],
            ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis));

        $response
            ->assertOk()
            ->assertSee('Review Percobaan Terakhir')
            ->assertSee('Pilihan Anda:')
            ->assertSee('Jawaban Benar:')
            ->assertSee('Apa warna badge sukses?');
    }

    public function test_superadmin_bisa_melihat_analitik_kuis_per_kesulitan(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $penggunaLain = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create();
        $kuis = KuisLms::factory()->create([
            'kursus_id' => $kursus->id,
            'gunakan_bank_soal' => true,
            'aktif' => true,
        ]);

        PercobaanKuisPengguna::query()->create([
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'percobaan_ke' => 1,
            'paket_soal' => [
                [
                    'kode' => 'bank_1',
                    'label_sumber' => 'Bank Soal',
                    'tingkat_kesulitan' => 'mudah',
                    'label_tingkat_kesulitan' => 'Mudah',
                    'pertanyaan' => 'Soal mudah',
                    'opsi_jawaban' => ['A', 'B', 'C', 'D'],
                    'indeks_jawaban_benar_tampil' => 1,
                ],
                [
                    'kode' => 'bank_2',
                    'label_sumber' => 'Bank Soal',
                    'tingkat_kesulitan' => 'sulit',
                    'label_tingkat_kesulitan' => 'Sulit',
                    'pertanyaan' => 'Soal sulit',
                    'opsi_jawaban' => ['A', 'B', 'C', 'D'],
                    'indeks_jawaban_benar_tampil' => 2,
                ],
            ],
            'jawaban_pengguna' => [
                'bank_1' => 1,
                'bank_2' => 0,
            ],
            'jumlah_benar' => 1,
            'jumlah_pertanyaan' => 2,
            'skor' => 50,
            'lulus' => false,
            'selesai_pada' => now(),
        ]);

        PercobaanKuisPengguna::query()->create([
            'kuis_lms_id' => $kuis->id,
            'user_id' => $penggunaLain->id,
            'percobaan_ke' => 1,
            'paket_soal' => [
                [
                    'kode' => 'bank_3',
                    'label_sumber' => 'Bank Soal',
                    'tingkat_kesulitan' => 'mudah',
                    'label_tingkat_kesulitan' => 'Mudah',
                    'pertanyaan' => 'Soal mudah 2',
                    'opsi_jawaban' => ['A', 'B', 'C', 'D'],
                    'indeks_jawaban_benar_tampil' => 3,
                ],
            ],
            'jawaban_pengguna' => [
                'bank_3' => 3,
            ],
            'jumlah_benar' => 1,
            'jumlah_pertanyaan' => 1,
            'skor' => 100,
            'lulus' => true,
            'selesai_pada' => now(),
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis));

        $response
            ->assertOk()
            ->assertSee('Analitik Kuis')
            ->assertSee('Mudah')
            ->assertSee('Sulit')
            ->assertSee('Percobaan');
    }

    public function test_halaman_detail_kuis_menampilkan_leaderboard_berdasarkan_skor_terbaik(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'name' => 'Pengguna Aktif',
        ]);
        $pesertaSatu = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'name' => 'Peserta Peringkat Satu',
        ]);
        $pesertaDua = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create([
            'name' => 'Peserta Peringkat Dua',
        ]);
        $kuis = KuisLms::factory()->create([
            'aktif' => true,
        ]);

        PercobaanKuisPengguna::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pesertaSatu->id,
            'percobaan_ke' => 1,
            'jumlah_benar' => 4,
            'jumlah_pertanyaan' => 4,
            'skor' => 100,
            'lulus' => true,
        ]);

        PercobaanKuisPengguna::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pesertaDua->id,
            'percobaan_ke' => 1,
            'jumlah_benar' => 3,
            'jumlah_pertanyaan' => 4,
            'skor' => 75,
            'lulus' => true,
        ]);

        PercobaanKuisPengguna::factory()->create([
            'kuis_lms_id' => $kuis->id,
            'user_id' => $pengguna->id,
            'percobaan_ke' => 1,
            'jumlah_benar' => 2,
            'jumlah_pertanyaan' => 4,
            'skor' => 50,
            'lulus' => false,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kuis.show', $kuis));

        $response
            ->assertOk()
            ->assertSee('Leaderboard')
            ->assertSeeInOrder([
                '#1 Peserta Peringkat Satu',
                '#2 Peserta Peringkat Dua',
                '#3 Pengguna Aktif',
            ]);
    }
}
