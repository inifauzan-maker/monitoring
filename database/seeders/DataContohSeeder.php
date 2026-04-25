<?php

namespace Database\Seeders;

use App\Enums\LevelAksesPengguna;
use App\Models\Artikel;
use App\Models\BankSoalLms;
use App\Models\DataSiswa;
use App\Models\HasilKuisPengguna;
use App\Models\KategoriArtikel;
use App\Models\KuisLms;
use App\Models\Kursus;
use App\Models\Lead;
use App\Models\LeadTindakLanjut;
use App\Models\MateriKursus;
use App\Models\NotifikasiPengguna;
use App\Models\PercobaanKuisPengguna;
use App\Models\PertanyaanKuis;
use App\Models\ProdukItem;
use App\Models\ProgresBelajarMateri;
use App\Models\Proyek;
use App\Models\TugasProyek;
use App\Models\User;
use Illuminate\Database\Seeder;

class DataContohSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', AkunAwalSeeder::emailAdmin())->first();
        $level3 = User::query()->where('email', AkunDemoSeeder::emailUntukLevel(LevelAksesPengguna::LEVEL_3))->first();
        $level5 = User::query()->where('email', AkunDemoSeeder::emailUntukLevel(LevelAksesPengguna::LEVEL_5))->first();

        if (! $superadmin || ! $level3 || ! $level5) {
            return;
        }

        $produk = $this->seedProduk();
        $this->seedLeads($superadmin, $level3);
        $this->seedSiswa($superadmin, $produk);
        $this->seedLms($superadmin, $level3, $level5);
        $this->seedProyek($superadmin, $level3, $level5);
        $this->seedArtikel($superadmin, $level3);
        $this->seedNotifikasi($superadmin, $level3, $level5);
    }

    /**
     * @return array<string, \App\Models\ProdukItem>
     */
    private function seedProduk(): array
    {
        $items = [
            'snbt_desain' => [
                'program' => 'senirupa',
                'tahun_ajaran' => '2026 - 2027',
                'kode_1' => 'SN',
                'kode_2' => 'BT',
                'kode_3' => 'DS',
                'kode_4' => '01',
                'nama' => 'Program Intensif SNBT Desain',
                'biaya_daftar' => 350000,
                'biaya_pendidikan' => 2850000,
                'discount' => 250000,
                'siswa' => 18,
            ],
            'arsitektur_weekend' => [
                'program' => 'arsitektur',
                'tahun_ajaran' => '2026 - 2027',
                'kode_1' => 'AR',
                'kode_2' => 'WD',
                'kode_3' => '26',
                'kode_4' => '02',
                'nama' => 'Kelas Weekend Arsitektur',
                'biaya_daftar' => 300000,
                'biaya_pendidikan' => 3250000,
                'discount' => 200000,
                'siswa' => 10,
            ],
            'gambar_anak_liburan' => [
                'program' => 'kelas_gambar_anak',
                'tahun_ajaran' => '2026 - 2027',
                'kode_1' => 'KA',
                'kode_2' => 'LB',
                'kode_3' => '26',
                'kode_4' => '03',
                'nama' => 'Kelas Gambar Anak Liburan',
                'biaya_daftar' => 150000,
                'biaya_pendidikan' => 850000,
                'discount' => 50000,
                'siswa' => 24,
            ],
        ];

        $hasil = [];

        foreach ($items as $kunci => $item) {
            $item['omzet'] = $this->hitungOmzetProduk($item);

            $hasil[$kunci] = ProdukItem::query()->updateOrCreate(
                [
                    'nama' => $item['nama'],
                    'tahun_ajaran' => $item['tahun_ajaran'],
                ],
                $item,
            );
        }

        return $hasil;
    }

    private function seedLeads(User $superadmin, User $level3): void
    {
        $leadA = Lead::query()->updateOrCreate(
            ['nomor_telepon' => '081234567801'],
            [
                'created_by' => $superadmin->id,
                'pic_id' => $level3->id,
                'nama_siswa' => 'Nadia Putri',
                'asal_sekolah' => 'SMA Negeri 5 Bandung',
                'channel' => 'Instagram',
                'sumber' => 'Ads/Iklan',
                'status' => 'follow_up',
                'jadwal_tindak_lanjut' => now()->addDays(1),
                'catatan' => 'Tertarik program SNBT Desain dan minta contoh portofolio.',
                'kontak_terakhir' => now()->subHours(5),
            ],
        );

        $leadB = Lead::query()->updateOrCreate(
            ['nomor_telepon' => '081234567802'],
            [
                'created_by' => $superadmin->id,
                'pic_id' => $level3->id,
                'nama_siswa' => 'Rizky Maulana',
                'asal_sekolah' => 'SMK Desain Komunikasi Visual Cimahi',
                'channel' => 'WhatsApp',
                'sumber' => 'Referensi',
                'status' => 'prospek',
                'jadwal_tindak_lanjut' => now()->addDays(2),
                'catatan' => 'Minta jadwal trial class minggu depan.',
                'kontak_terakhir' => now()->subDay(),
            ],
        );

        $leadC = Lead::query()->updateOrCreate(
            ['nomor_telepon' => '081234567803'],
            [
                'created_by' => $superadmin->id,
                'pic_id' => $superadmin->id,
                'nama_siswa' => 'Aulia Rahman',
                'asal_sekolah' => 'SMA Labschool Jakarta',
                'channel' => 'Website',
                'sumber' => 'Organik',
                'status' => 'closing',
                'jadwal_tindak_lanjut' => now()->subDay(),
                'catatan' => 'Sudah menyatakan siap daftar dan minta invoice.',
                'kontak_terakhir' => now()->subHours(2),
            ],
        );

        LeadTindakLanjut::query()->updateOrCreate(
            [
                'lead_id' => $leadA->id,
                'catatan' => 'Kirim contoh portofolio SNBT melalui WhatsApp.',
            ],
            [
                'user_id' => $level3->id,
                'jadwal_tindak_lanjut' => now()->addHours(6),
                'status' => 'direncanakan',
            ],
        );

        LeadTindakLanjut::query()->updateOrCreate(
            [
                'lead_id' => $leadC->id,
                'catatan' => 'Konfirmasi bukti transfer biaya daftar.',
            ],
            [
                'user_id' => $superadmin->id,
                'jadwal_tindak_lanjut' => now()->subHours(3),
                'status' => 'selesai',
            ],
        );

        LeadTindakLanjut::query()->updateOrCreate(
            [
                'lead_id' => $leadB->id,
                'catatan' => 'Jadwalkan trial class arsitektur akhir pekan.',
            ],
            [
                'user_id' => $level3->id,
                'jadwal_tindak_lanjut' => now()->addDays(3),
                'status' => 'direncanakan',
            ],
        );
    }

    /**
     * @param  array<string, \App\Models\ProdukItem>  $produk
     */
    private function seedSiswa(User $superadmin, array $produk): void
    {
        DataSiswa::query()->updateOrCreate(
            ['nomor_invoice' => 'INV-DEMO-001'],
            [
                'produk_item_id' => $produk['snbt_desain']->id,
                'divalidasi_oleh' => $superadmin->id,
                'nama_lengkap' => 'Nadia Putri',
                'asal_sekolah' => 'SMA Negeri 5 Bandung',
                'tingkat_kelas' => '12',
                'jurusan' => 'IPA',
                'nomor_telepon' => '081234567801',
                'nama_orang_tua' => 'Budi Santoso',
                'nomor_telepon_orang_tua' => '081234560001',
                'lokasi_belajar' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Bandung',
                'program' => $produk['snbt_desain']->program,
                'nama_program' => $produk['snbt_desain']->nama,
                'sistem_pembayaran' => 'angsuran',
                'status_validasi' => 'validated',
                'tanggal_validasi' => now()->subDays(4),
                'total_invoice' => 2950000,
                'jumlah_pembayaran' => 1500000,
                'sisa_tagihan' => 1450000,
                'status_pembayaran' => 'sebagian',
                'tanggal_pembayaran' => now()->subDays(2),
                'keterangan' => 'Sudah masuk kelas orientasi.',
            ],
        );

        DataSiswa::query()->updateOrCreate(
            ['nomor_invoice' => 'INV-DEMO-002'],
            [
                'produk_item_id' => $produk['arsitektur_weekend']->id,
                'divalidasi_oleh' => $superadmin->id,
                'nama_lengkap' => 'Rizky Maulana',
                'asal_sekolah' => 'SMK Desain Komunikasi Visual Cimahi',
                'tingkat_kelas' => 'Gap Year',
                'jurusan' => 'Desain',
                'nomor_telepon' => '081234567802',
                'nama_orang_tua' => 'Rina Marlina',
                'nomor_telepon_orang_tua' => '081234560002',
                'lokasi_belajar' => 'Online',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Cimahi',
                'program' => $produk['arsitektur_weekend']->program,
                'nama_program' => $produk['arsitektur_weekend']->nama,
                'sistem_pembayaran' => 'lunas',
                'status_validasi' => 'validated',
                'tanggal_validasi' => now()->subDays(6),
                'total_invoice' => 3350000,
                'jumlah_pembayaran' => 3350000,
                'sisa_tagihan' => 0,
                'status_pembayaran' => 'lunas',
                'tanggal_pembayaran' => now()->subDays(5),
                'keterangan' => 'Sudah menerima modul awal.',
            ],
        );

        DataSiswa::query()->updateOrCreate(
            ['nomor_invoice' => 'INV-DEMO-003'],
            [
                'produk_item_id' => $produk['gambar_anak_liburan']->id,
                'divalidasi_oleh' => $superadmin->id,
                'nama_lengkap' => 'Alya Maharani',
                'asal_sekolah' => 'SD Kreatif Nusantara',
                'tingkat_kelas' => '10',
                'jurusan' => 'IPA',
                'nomor_telepon' => '081234567804',
                'nama_orang_tua' => 'Dewi Pratiwi',
                'nomor_telepon_orang_tua' => '081234560003',
                'lokasi_belajar' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Bandung',
                'program' => $produk['gambar_anak_liburan']->program,
                'nama_program' => $produk['gambar_anak_liburan']->nama,
                'sistem_pembayaran' => 'lunas',
                'status_validasi' => 'pending',
                'tanggal_validasi' => null,
                'total_invoice' => 950000,
                'jumlah_pembayaran' => 0,
                'sisa_tagihan' => 950000,
                'status_pembayaran' => 'belum_bayar',
                'tanggal_pembayaran' => null,
                'keterangan' => 'Menunggu konfirmasi pembayaran awal.',
            ],
        );
    }

    private function seedLms(User $superadmin, User $level3, User $level5): void
    {
        $kursusPortofolio = Kursus::query()->updateOrCreate(
            ['slug' => 'strategi-portofolio-snbt'],
            [
                'judul' => 'Strategi Portofolio SNBT',
                'ringkasan' => 'Panduan menyusun portofolio desain yang terarah untuk persiapan seleksi SNBT.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
            ],
        );

        $kursusKonten = Kursus::query()->updateOrCreate(
            ['slug' => 'fundamental-konten-media-sosial'],
            [
                'judul' => 'Fundamental Konten Media Sosial',
                'ringkasan' => 'Dasar membangun konten, ritme publikasi, dan evaluasi performa untuk tim kampanye.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?auto=format&fit=crop&w=1200&q=80',
            ],
        );

        $materiPortofolio1 = MateriKursus::query()->updateOrCreate(
            ['kursus_id' => $kursusPortofolio->id, 'judul' => 'Mindset dan Struktur Portofolio'],
            [
                'youtube_id' => 'dQw4w9WgXcQ',
                'durasi_detik' => 900,
                'urutan' => 1,
            ],
        );

        $materiPortofolio2 = MateriKursus::query()->updateOrCreate(
            ['kursus_id' => $kursusPortofolio->id, 'judul' => 'Menyusun Narasi Visual yang Jelas'],
            [
                'youtube_id' => '3JZ_D3ELwOQ',
                'durasi_detik' => 1020,
                'urutan' => 2,
            ],
        );

        $materiPortofolio3 = MateriKursus::query()->updateOrCreate(
            ['kursus_id' => $kursusPortofolio->id, 'judul' => 'Checklist Final Sebelum Submit'],
            [
                'youtube_id' => 'M7lc1UVf-VE',
                'durasi_detik' => 720,
                'urutan' => 3,
            ],
        );

        $materiKonten1 = MateriKursus::query()->updateOrCreate(
            ['kursus_id' => $kursusKonten->id, 'judul' => 'Pilar Konten dan Tujuan Kampanye'],
            [
                'youtube_id' => 'ysz5S6PUM-U',
                'durasi_detik' => 840,
                'urutan' => 1,
            ],
        );

        $materiKonten2 = MateriKursus::query()->updateOrCreate(
            ['kursus_id' => $kursusKonten->id, 'judul' => 'Kalender Konten Mingguan'],
            [
                'youtube_id' => 'aqz-KE-bpKQ',
                'durasi_detik' => 780,
                'urutan' => 2,
            ],
        );

        $bankSoalA = BankSoalLms::query()->updateOrCreate(
            [
                'kursus_id' => $kursusPortofolio->id,
                'pertanyaan' => 'Bagian pertama portofolio sebaiknya menampilkan apa?',
            ],
            [
                'opsi_jawaban' => [
                    'Karya terbaik dan paling relevan',
                    'Daftar hobi pribadi',
                    'Catatan nilai rapor',
                    'Riwayat organisasi sekolah',
                ],
                'indeks_jawaban_benar' => 0,
                'tingkat_kesulitan' => 'mudah',
                'aktif' => true,
            ],
        );

        $bankSoalB = BankSoalLms::query()->updateOrCreate(
            [
                'kursus_id' => $kursusPortofolio->id,
                'pertanyaan' => 'Narasi visual yang baik terutama membantu asesor untuk apa?',
            ],
            [
                'opsi_jawaban' => [
                    'Mengetahui warna favorit peserta',
                    'Memahami proses berpikir dan keputusan desain',
                    'Melihat jumlah followers Instagram',
                    'Membandingkan harga laptop peserta',
                ],
                'indeks_jawaban_benar' => 1,
                'tingkat_kesulitan' => 'menengah',
                'aktif' => true,
            ],
        );

        $bankSoalC = BankSoalLms::query()->updateOrCreate(
            [
                'kursus_id' => $kursusPortofolio->id,
                'pertanyaan' => 'Checklist final sebelum submit paling penting untuk mencegah apa?',
            ],
            [
                'opsi_jawaban' => [
                    'Tampilan portofolio terlalu singkat',
                    'Judul proyek terlalu panjang',
                    'Kesalahan file, urutan, dan kelengkapan dokumen',
                    'Komentar dari teman sekelas',
                ],
                'indeks_jawaban_benar' => 2,
                'tingkat_kesulitan' => 'sulit',
                'aktif' => true,
            ],
        );

        $kuisPortofolio = KuisLms::query()->updateOrCreate(
            ['judul' => 'Kuis Dasar Portofolio SNBT'],
            [
                'deskripsi' => 'Kuis pengantar untuk memastikan peserta memahami alur dasar penyusunan portofolio.',
                'target_tipe' => 'kursus',
                'kursus_id' => $kursusPortofolio->id,
                'materi_kursus_id' => null,
                'nilai_lulus' => 70,
                'durasi_menit' => 15,
                'maksimal_percobaan' => 3,
                'gunakan_bank_soal' => true,
                'acak_soal' => true,
                'acak_opsi_jawaban' => true,
                'jumlah_soal_tampil' => 3,
                'aktif' => true,
            ],
        );

        $kuisPortofolio->bankSoal()->syncWithoutDetaching([
            $bankSoalA->id => ['urutan' => 1],
            $bankSoalB->id => ['urutan' => 2],
            $bankSoalC->id => ['urutan' => 3],
        ]);

        $kuisPortofolio->bankSoal()->updateExistingPivot($bankSoalA->id, ['urutan' => 1]);
        $kuisPortofolio->bankSoal()->updateExistingPivot($bankSoalB->id, ['urutan' => 2]);
        $kuisPortofolio->bankSoal()->updateExistingPivot($bankSoalC->id, ['urutan' => 3]);

        $kuisMateri = KuisLms::query()->updateOrCreate(
            ['judul' => 'Kuis Materi Kalender Konten'],
            [
                'deskripsi' => 'Evaluasi singkat setelah mempelajari penyusunan kalender konten mingguan.',
                'target_tipe' => 'materi',
                'kursus_id' => $kursusKonten->id,
                'materi_kursus_id' => $materiKonten2->id,
                'nilai_lulus' => 75,
                'durasi_menit' => 10,
                'maksimal_percobaan' => 2,
                'gunakan_bank_soal' => false,
                'acak_soal' => false,
                'acak_opsi_jawaban' => false,
                'jumlah_soal_tampil' => null,
                'aktif' => true,
            ],
        );

        PertanyaanKuis::query()->updateOrCreate(
            [
                'kuis_lms_id' => $kuisMateri->id,
                'pertanyaan' => 'Tujuan kalender konten mingguan adalah apa?',
            ],
            [
                'opsi_jawaban' => [
                    'Menunda proses publikasi',
                    'Menjadwalkan ide, format, dan distribusi konten secara konsisten',
                    'Menghapus kebutuhan evaluasi',
                    'Membatasi ide hanya dari satu platform',
                ],
                'indeks_jawaban_benar' => 1,
                'urutan' => 1,
            ],
        );

        PertanyaanKuis::query()->updateOrCreate(
            [
                'kuis_lms_id' => $kuisMateri->id,
                'pertanyaan' => 'Kolom wajib yang baik di kalender konten minimal mencakup apa?',
            ],
            [
                'opsi_jawaban' => [
                    'Tanggal, topik, format, PIC, dan target publish',
                    'Nama admin dan warna favorit tim',
                    'Jumlah komentar negatif',
                    'Password akun sosial media',
                ],
                'indeks_jawaban_benar' => 0,
                'urutan' => 2,
            ],
        );

        ProgresBelajarMateri::query()->updateOrCreate(
            [
                'user_id' => $level5->id,
                'materi_kursus_id' => $materiPortofolio1->id,
            ],
            [
                'detik_terakhir' => 900,
                'persen_progres' => 100,
                'selesai_pada' => now()->subDays(2),
            ],
        );

        ProgresBelajarMateri::query()->updateOrCreate(
            [
                'user_id' => $level5->id,
                'materi_kursus_id' => $materiPortofolio2->id,
            ],
            [
                'detik_terakhir' => 510,
                'persen_progres' => 50,
                'selesai_pada' => null,
            ],
        );

        ProgresBelajarMateri::query()->updateOrCreate(
            [
                'user_id' => $level3->id,
                'materi_kursus_id' => $materiKonten1->id,
            ],
            [
                'detik_terakhir' => 840,
                'persen_progres' => 100,
                'selesai_pada' => now()->subDay(),
            ],
        );

        ProgresBelajarMateri::query()->updateOrCreate(
            [
                'user_id' => $level3->id,
                'materi_kursus_id' => $materiKonten2->id,
            ],
            [
                'detik_terakhir' => 390,
                'persen_progres' => 50,
                'selesai_pada' => null,
            ],
        );

        $paketPortofolio = [
            [
                'kode' => 'bank_'.$bankSoalA->id,
                'label_sumber' => 'Bank Soal',
                'tingkat_kesulitan' => 'mudah',
                'label_tingkat_kesulitan' => 'Mudah',
                'pertanyaan' => $bankSoalA->pertanyaan,
                'opsi_jawaban' => $bankSoalA->opsi_jawaban,
                'indeks_jawaban_benar_tampil' => $bankSoalA->indeks_jawaban_benar,
            ],
            [
                'kode' => 'bank_'.$bankSoalB->id,
                'label_sumber' => 'Bank Soal',
                'tingkat_kesulitan' => 'menengah',
                'label_tingkat_kesulitan' => 'Menengah',
                'pertanyaan' => $bankSoalB->pertanyaan,
                'opsi_jawaban' => $bankSoalB->opsi_jawaban,
                'indeks_jawaban_benar_tampil' => $bankSoalB->indeks_jawaban_benar,
            ],
            [
                'kode' => 'bank_'.$bankSoalC->id,
                'label_sumber' => 'Bank Soal',
                'tingkat_kesulitan' => 'sulit',
                'label_tingkat_kesulitan' => 'Sulit',
                'pertanyaan' => $bankSoalC->pertanyaan,
                'opsi_jawaban' => $bankSoalC->opsi_jawaban,
                'indeks_jawaban_benar_tampil' => $bankSoalC->indeks_jawaban_benar,
            ],
        ];

        PercobaanKuisPengguna::query()->updateOrCreate(
            [
                'kuis_lms_id' => $kuisPortofolio->id,
                'user_id' => $level5->id,
                'percobaan_ke' => 1,
            ],
            [
                'paket_soal' => $paketPortofolio,
                'jawaban_pengguna' => [
                    'bank_'.$bankSoalA->id => 0,
                    'bank_'.$bankSoalB->id => 1,
                    'bank_'.$bankSoalC->id => 2,
                ],
                'jumlah_benar' => 3,
                'jumlah_pertanyaan' => 3,
                'skor' => 100,
                'lulus' => true,
                'selesai_pada' => now()->subDay(),
            ],
        );

        HasilKuisPengguna::query()->updateOrCreate(
            [
                'kuis_lms_id' => $kuisPortofolio->id,
                'user_id' => $level5->id,
            ],
            [
                'jawaban_pengguna' => [
                    'bank_'.$bankSoalA->id => 0,
                    'bank_'.$bankSoalB->id => 1,
                    'bank_'.$bankSoalC->id => 2,
                ],
                'jumlah_benar' => 3,
                'jumlah_pertanyaan' => 3,
                'skor' => 100,
                'lulus' => true,
                'jumlah_percobaan' => 1,
                'selesai_pada' => now()->subDay(),
            ],
        );

        PercobaanKuisPengguna::query()->updateOrCreate(
            [
                'kuis_lms_id' => $kuisPortofolio->id,
                'user_id' => $level3->id,
                'percobaan_ke' => 1,
            ],
            [
                'paket_soal' => $paketPortofolio,
                'jawaban_pengguna' => [
                    'bank_'.$bankSoalA->id => 0,
                    'bank_'.$bankSoalB->id => 0,
                    'bank_'.$bankSoalC->id => 2,
                ],
                'jumlah_benar' => 2,
                'jumlah_pertanyaan' => 3,
                'skor' => 67,
                'lulus' => false,
                'selesai_pada' => now()->subHours(12),
            ],
        );

        HasilKuisPengguna::query()->updateOrCreate(
            [
                'kuis_lms_id' => $kuisPortofolio->id,
                'user_id' => $level3->id,
            ],
            [
                'jawaban_pengguna' => [
                    'bank_'.$bankSoalA->id => 0,
                    'bank_'.$bankSoalB->id => 0,
                    'bank_'.$bankSoalC->id => 2,
                ],
                'jumlah_benar' => 2,
                'jumlah_pertanyaan' => 3,
                'skor' => 67,
                'lulus' => false,
                'jumlah_percobaan' => 1,
                'selesai_pada' => now()->subHours(12),
            ],
        );

        $pertanyaanMateri = $kuisMateri->pertanyaan()->orderBy('urutan')->get();

        if ($pertanyaanMateri->count() >= 2) {
            $pertanyaanPertama = $pertanyaanMateri[0];
            $pertanyaanKedua = $pertanyaanMateri[1];

            PercobaanKuisPengguna::query()->updateOrCreate(
                [
                    'kuis_lms_id' => $kuisMateri->id,
                    'user_id' => $superadmin->id,
                    'percobaan_ke' => 1,
                ],
                [
                    'paket_soal' => [
                        [
                            'kode' => 'pertanyaan_'.$pertanyaanPertama->id,
                            'label_sumber' => 'Manual',
                            'tingkat_kesulitan' => null,
                            'label_tingkat_kesulitan' => 'Manual',
                            'pertanyaan' => $pertanyaanPertama->pertanyaan,
                            'opsi_jawaban' => $pertanyaanPertama->opsi_jawaban,
                            'indeks_jawaban_benar_tampil' => $pertanyaanPertama->indeks_jawaban_benar,
                        ],
                        [
                            'kode' => 'pertanyaan_'.$pertanyaanKedua->id,
                            'label_sumber' => 'Manual',
                            'tingkat_kesulitan' => null,
                            'label_tingkat_kesulitan' => 'Manual',
                            'pertanyaan' => $pertanyaanKedua->pertanyaan,
                            'opsi_jawaban' => $pertanyaanKedua->opsi_jawaban,
                            'indeks_jawaban_benar_tampil' => $pertanyaanKedua->indeks_jawaban_benar,
                        ],
                    ],
                    'jawaban_pengguna' => [
                        'pertanyaan_'.$pertanyaanPertama->id => $pertanyaanPertama->indeks_jawaban_benar,
                        'pertanyaan_'.$pertanyaanKedua->id => $pertanyaanKedua->indeks_jawaban_benar,
                    ],
                    'jumlah_benar' => 2,
                    'jumlah_pertanyaan' => 2,
                    'skor' => 100,
                    'lulus' => true,
                    'selesai_pada' => now()->subHours(8),
                ],
            );

            HasilKuisPengguna::query()->updateOrCreate(
                [
                    'kuis_lms_id' => $kuisMateri->id,
                    'user_id' => $superadmin->id,
                ],
                [
                    'jawaban_pengguna' => [
                        'pertanyaan_'.$pertanyaanPertama->id => $pertanyaanPertama->indeks_jawaban_benar,
                        'pertanyaan_'.$pertanyaanKedua->id => $pertanyaanKedua->indeks_jawaban_benar,
                    ],
                    'jumlah_benar' => 2,
                    'jumlah_pertanyaan' => 2,
                    'skor' => 100,
                    'lulus' => true,
                    'jumlah_percobaan' => 1,
                    'selesai_pada' => now()->subHours(8),
                ],
            );
        }
    }

    private function seedProyek(User $superadmin, User $level3, User $level5): void
    {
        $proyek = Proyek::query()->updateOrCreate(
            ['kode_project' => 'PRJ-DEMO-001'],
            [
                'nama_project' => 'Revamp Dashboard Simarketing',
                'klien' => 'Internal Simarketing',
                'status_project' => 'berjalan',
                'prioritas_project' => 'tinggi',
                'tanggal_mulai' => now()->subDays(7)->toDateString(),
                'tanggal_target_selesai' => now()->addDays(21)->toDateString(),
                'tanggal_selesai' => null,
                'deskripsi_project' => 'Penyempurnaan modul dashboard, LMS, dan pelaporan agar siap dipakai tim operasional.',
                'alur_kerja' => 'Analisis kebutuhan, implementasi, review internal, uji coba pengguna, lalu rilis bertahap.',
                'sop_ringkas' => 'Setiap perubahan modul wajib melalui board tugas, review, dan pengujian sebelum dipindahkan ke status selesai.',
                'penanggung_jawab_id' => $superadmin->id,
                'skor_evaluasi' => 88,
                'catatan_evaluasi' => 'Proyek berjalan sesuai target, tetapi butuh penguatan dokumentasi QA.',
                'dibuat_oleh' => $superadmin->id,
            ],
        );

        TugasProyek::query()->updateOrCreate(
            [
                'proyek_id' => $proyek->id,
                'judul_tugas' => 'Susun ulang menu LMS dan bank soal',
            ],
            [
                'deskripsi_tugas' => 'Rapikan struktur sidebar dan alur pengelolaan bank soal untuk admin.',
                'status_tugas' => 'selesai',
                'prioritas_tugas' => 'tinggi',
                'persentase_progres' => 100,
                'penanggung_jawab_id' => $superadmin->id,
                'tanggal_mulai' => now()->subDays(6)->toDateString(),
                'tanggal_target' => now()->subDays(2)->toDateString(),
                'tanggal_selesai' => now()->subDays(2)->toDateString(),
                'catatan_tugas' => 'Selesai dan sudah masuk tahap verifikasi.',
                'urutan' => 1,
            ],
        );

        TugasProyek::query()->updateOrCreate(
            [
                'proyek_id' => $proyek->id,
                'judul_tugas' => 'Lengkapi data contoh untuk demo internal',
            ],
            [
                'deskripsi_tugas' => 'Siapkan produk, leads, siswa, kursus, kuis, dan artikel contoh untuk presentasi internal.',
                'status_tugas' => 'review',
                'prioritas_tugas' => 'sedang',
                'persentase_progres' => 80,
                'penanggung_jawab_id' => $level3->id,
                'tanggal_mulai' => now()->subDays(3)->toDateString(),
                'tanggal_target' => now()->addDays(1)->toDateString(),
                'tanggal_selesai' => null,
                'catatan_tugas' => 'Menunggu review final sebelum ditandai selesai.',
                'urutan' => 2,
            ],
        );

        TugasProyek::query()->updateOrCreate(
            [
                'proyek_id' => $proyek->id,
                'judul_tugas' => 'Uji leaderboard dan limit percobaan kuis',
            ],
            [
                'deskripsi_tugas' => 'Pastikan leaderboard kuis dan pembatasan percobaan tampil benar untuk user aktif.',
                'status_tugas' => 'berjalan',
                'prioritas_tugas' => 'tinggi',
                'persentase_progres' => 55,
                'penanggung_jawab_id' => $level5->id,
                'tanggal_mulai' => now()->subDay()->toDateString(),
                'tanggal_target' => now()->addDays(3)->toDateString(),
                'tanggal_selesai' => null,
                'catatan_tugas' => 'Masih pengecekan edge case pada submit otomatis timer.',
                'urutan' => 3,
            ],
        );
    }

    private function seedArtikel(User $superadmin, User $level3): void
    {
        $kategoriBelajar = KategoriArtikel::query()->updateOrCreate(
            ['slug' => 'strategi-belajar'],
            [
                'nama' => 'Strategi Belajar',
                'deskripsi' => 'Artikel tips belajar, fokus, dan penguatan kebiasaan belajar yang efektif.',
            ],
        );

        $kategoriKampanye = KategoriArtikel::query()->updateOrCreate(
            ['slug' => 'kampanye-digital'],
            [
                'nama' => 'Kampanye Digital',
                'deskripsi' => 'Artikel seputar konten, distribusi, dan evaluasi performa kampanye digital.',
            ],
        );

        Artikel::query()->updateOrCreate(
            ['slug' => 'cara-menyusun-portofolio-desain-yang-terarah'],
            [
                'judul' => 'Cara Menyusun Portofolio Desain yang Terarah',
                'kata_kunci_utama' => 'portofolio desain snbt',
                'ringkasan' => 'Panduan ringkas menyusun portofolio desain yang rapi, relevan, dan siap dinilai asesor seleksi.',
                'konten' => '<p>Portofolio yang baik harus menunjukkan arah berpikir, kualitas eksekusi, dan konteks tiap karya.</p><p>Mulailah dari karya yang paling kuat, lalu susun narasi visual yang mudah dipahami.</p><p>Lengkapi dengan keterangan proses, tujuan desain, dan hasil akhir yang jelas.</p>',
                'kategori_artikel_id' => $kategoriBelajar->id,
                'penulis_id' => $superadmin->id,
                'tingkat_keahlian' => 'menengah',
                'bio_penulis' => 'Mentor internal monitoring untuk modul pembelajaran desain dan strategi portofolio.',
                'sumber_referensi' => [
                    'https://example.com/referensi-portofolio-1',
                    'https://example.com/referensi-portofolio-2',
                ],
                'judul_seo' => 'Cara Menyusun Portofolio Desain untuk SNBT',
                'deskripsi_seo' => 'Langkah praktis menyusun portofolio desain yang terarah untuk kebutuhan seleksi dan presentasi karya.',
                'outline_seo' => "# Portofolio Desain\n\n## Tujuan Portofolio\n## Struktur Isi\n## Checklist Akhir",
                'checklist_kesiapan' => [
                    'keyword_sudah_dicek' => true,
                    'metadata_seo_final' => true,
                    'referensi_sudah_valid' => true,
                    'konten_sudah_dicek' => true,
                    'gambar_unggulan_siap' => true,
                ],
                'gambar_unggulan_path' => null,
                'alt_gambar_unggulan' => 'Portofolio desain terstruktur',
                'sudah_diterbitkan' => true,
                'diterbitkan_pada' => now()->subDays(5),
            ],
        );

        Artikel::query()->updateOrCreate(
            ['slug' => 'kalender-konten-mingguan-untuk-tim-kampanye'],
            [
                'judul' => 'Kalender Konten Mingguan untuk Tim Kampanye',
                'kata_kunci_utama' => 'kalender konten mingguan',
                'ringkasan' => 'Template kerja mingguan agar tim kampanye lebih konsisten dalam menyiapkan, menjadwalkan, dan mengevaluasi konten.',
                'konten' => '<p>Kalender konten membantu tim menjaga ritme publikasi dan membagi tanggung jawab secara jelas.</p><p>Minimal ada kolom tanggal, topik, format, kanal, CTA, dan penanggung jawab.</p><p>Setelah terbit, isi hasil evaluasi singkat agar ide berikutnya makin terarah.</p>',
                'kategori_artikel_id' => $kategoriKampanye->id,
                'penulis_id' => $level3->id,
                'tingkat_keahlian' => 'pemula',
                'bio_penulis' => 'Tim editorial kampanye yang fokus pada perencanaan konten dan evaluasi distribusi.',
                'sumber_referensi' => [
                    'https://example.com/referensi-konten-1',
                    'https://example.com/referensi-konten-2',
                ],
                'judul_seo' => 'Kalender Konten Mingguan untuk Tim Kampanye',
                'deskripsi_seo' => 'Susun kalender konten mingguan yang rapi agar produksi, distribusi, dan evaluasi konten lebih konsisten.',
                'outline_seo' => "# Kalender Konten Mingguan\n\n## Komponen Utama\n## Contoh Kolom\n## Evaluasi",
                'checklist_kesiapan' => [
                    'keyword_sudah_dicek' => true,
                    'metadata_seo_final' => true,
                    'referensi_sudah_valid' => true,
                    'konten_sudah_dicek' => true,
                    'gambar_unggulan_siap' => true,
                ],
                'gambar_unggulan_path' => null,
                'alt_gambar_unggulan' => 'Kalender konten kampanye mingguan',
                'sudah_diterbitkan' => true,
                'diterbitkan_pada' => now()->subDays(2),
            ],
        );

        Artikel::query()->updateOrCreate(
            ['slug' => 'draft-brief-kampanye-bulanan'],
            [
                'judul' => 'Draft Brief Kampanye Bulanan',
                'kata_kunci_utama' => 'brief kampanye bulanan',
                'ringkasan' => 'Draft awal untuk menyusun brief kampanye bulanan tim kreatif dan distribusi.',
                'konten' => '<p>Dokumen ini masih berupa draft dan menunggu finalisasi target, CTA, dan format konten utama.</p>',
                'kategori_artikel_id' => $kategoriKampanye->id,
                'penulis_id' => $level3->id,
                'tingkat_keahlian' => 'menengah',
                'bio_penulis' => 'Tim editorial kampanye.',
                'sumber_referensi' => [
                    'https://example.com/referensi-brief-1',
                ],
                'judul_seo' => 'Draft Brief Kampanye Bulanan',
                'deskripsi_seo' => 'Draft awal brief kampanye yang masih menunggu penyempurnaan target dan alur distribusi.',
                'outline_seo' => null,
                'checklist_kesiapan' => [
                    'keyword_sudah_dicek' => false,
                    'metadata_seo_final' => false,
                    'referensi_sudah_valid' => false,
                    'konten_sudah_dicek' => false,
                    'gambar_unggulan_siap' => false,
                ],
                'gambar_unggulan_path' => null,
                'alt_gambar_unggulan' => null,
                'sudah_diterbitkan' => false,
                'diterbitkan_pada' => null,
            ],
        );
    }

    private function seedNotifikasi(User $superadmin, User $level3, User $level5): void
    {
        $notifikasi = [
            [
                'user_id' => $level3->id,
                'judul' => 'Review tugas project',
                'pesan' => 'Tugas "Lengkapi data contoh untuk demo internal" sedang menunggu review final.',
                'tipe' => 'warning',
                'tautan' => route('proyek.detail_tugas'),
            ],
            [
                'user_id' => $level5->id,
                'judul' => 'Progres tugas perlu diperbarui',
                'pesan' => 'Tugas "Uji leaderboard dan limit percobaan kuis" masih berjalan. Perbarui progres terbaru jika ada perubahan.',
                'tipe' => 'info',
                'tautan' => route('proyek.detail_tugas'),
            ],
            [
                'user_id' => $superadmin->id,
                'judul' => 'Kuis LMS siap dipantau',
                'pesan' => 'Leaderboard dan batas percobaan kuis demo sudah terisi data contoh untuk validasi internal.',
                'tipe' => 'success',
                'tautan' => route('lms.kuis'),
            ],
        ];

        foreach ($notifikasi as $item) {
            NotifikasiPengguna::query()->updateOrCreate(
                [
                    'user_id' => $item['user_id'],
                    'judul' => $item['judul'],
                ],
                [
                    'pesan' => $item['pesan'],
                    'tipe' => $item['tipe'],
                    'tautan' => $item['tautan'],
                ],
            );
        }
    }

    /**
     * @param  array<string, int|string>  $item
     */
    private function hitungOmzetProduk(array $item): int
    {
        return max(
            0,
            ((int) $item['biaya_daftar'] + (int) $item['biaya_pendidikan'] - (int) $item['discount']) * (int) $item['siswa'],
        );
    }
}
