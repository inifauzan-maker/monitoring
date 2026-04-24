<?php

namespace App\Http\Controllers;

use App\Enums\LevelAksesPengguna;
use App\Enums\ProfilBerandaPengguna;
use App\Models\Artikel;
use App\Models\DataSiswa;
use App\Models\HasilKuisPengguna;
use App\Models\KuisLms;
use App\Models\Kursus;
use App\Models\Lead;
use App\Models\LinkPengguna;
use App\Models\LogAktivitas;
use App\Models\MateriKursus;
use App\Models\ProdukItem;
use App\Models\ProgresBelajarMateri;
use App\Models\Proyek;
use App\Models\PemetaanBerandaLevel;
use App\Models\StatistikLinkHarian;
use App\Models\TugasProyek;
use App\Models\User;
use App\Support\AnalitikLinkPublik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkPublikController extends Controller
{
    public function __construct(
        protected AnalitikLinkPublik $analitikLinkPublik,
    ) {
    }

    public function root(Request $request): View|RedirectResponse
    {
        $pengguna = User::resolusikanDariDomainKustomLink($request->getHost());

        if ($pengguna) {
            return $this->renderHalamanPublik($request, $pengguna, true);
        }

        if ($request->user()) {
            return $this->renderBerandaAplikasi($request->user());
        }

        return redirect()->route('login');
    }

    public function show(Request $request, User $pengguna): View
    {
        return $this->renderHalamanPublik(
            $request,
            $pengguna,
            $pengguna->cocokDomainKustomLink($request->getHost())
        );
    }

    public function cta(Request $request, User $pengguna): RedirectResponse
    {
        return $this->prosesCta(
            $request,
            $pengguna,
            $pengguna->cocokDomainKustomLink($request->getHost())
        );
    }

    public function domainCta(Request $request): RedirectResponse
    {
        return $this->prosesCta($request, $this->penggunaDariDomainKustom($request), true);
    }

    public function buka(Request $request, User $pengguna, LinkPengguna $linkPengguna): RedirectResponse
    {
        return $this->prosesBuka(
            $request,
            $pengguna,
            $linkPengguna,
            $pengguna->cocokDomainKustomLink($request->getHost())
        );
    }

    public function domainBuka(Request $request, LinkPengguna $linkPengguna): RedirectResponse
    {
        return $this->prosesBuka($request, $this->penggunaDariDomainKustom($request), $linkPengguna, true);
    }

    private function renderHalamanPublik(Request $request, User $pengguna, bool $menggunakanDomainKustom): View
    {
        $pengguna = $this->siapkanPenggunaPublik($request, $pengguna, $menggunakanDomainKustom);
        $sourceAktif = $this->analitikLinkPublik->normalisasiSumber(
            $request->query('source') ?? $request->query('utm_source')
        );
        $parameterSource = array_filter([
            'source' => $sourceAktif,
        ], fn (mixed $nilai) => filled($nilai));
        $linkAktif = $pengguna
            ->linkPengguna()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function (LinkPengguna $link) use ($pengguna, $menggunakanDomainKustom, $parameterSource) {
                $urlPublik = $menggunakanDomainKustom
                    ? route('publik.domain_link.buka', [
                        'linkPengguna' => $link,
                        ...$parameterSource,
                    ], false)
                    : route('publik.link.buka', [
                        'pengguna' => $pengguna,
                        'linkPengguna' => $link,
                        ...$parameterSource,
                    ]);

                $link->setAttribute('url_publik', $urlPublik);

                return $link;
            });
        $urlCtaPublik = null;

        if ($pengguna->urlCtaLinkPublik()) {
            $urlCtaPublik = $menggunakanDomainKustom
                ? route('publik.domain_link.cta', $parameterSource, false)
                : route('publik.link.cta', [
                    'pengguna' => $pengguna,
                    ...$parameterSource,
                ]);
        }

        $this->analitikLinkPublik->catat($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, $request);

        return view('tools.link.publik', [
            'pengguna' => $pengguna,
            'linkAktif' => $linkAktif,
            'judulHalaman' => $pengguna->judulLinkPublik(),
            'namaPublik' => $pengguna->namaTampilLinkPublik(),
            'avatarPublikUrl' => $pengguna->avatarLinkPublikUrl(),
            'headlineHalaman' => $pengguna->headlineLinkPublik(),
            'bioHalaman' => $pengguna->bioLinkPublik(),
            'labelCta' => $pengguna->labelCtaLinkPublik(),
            'urlCta' => $pengguna->urlCtaLinkPublik(),
            'urlCtaPublik' => $urlCtaPublik,
            'temaHalaman' => $pengguna->temaLinkKonfigurasi(),
            'sourceAktif' => $sourceAktif,
            'menggunakanDomainKustom' => $menggunakanDomainKustom,
        ]);
    }

    private function renderBerandaAplikasi(User $pengguna): View
    {
        $totalProduk = ProdukItem::query()->count();
        $targetOmzet = (int) ProdukItem::query()->sum('omzet');
        $targetSiswa = (int) ProdukItem::query()->sum('siswa');

        $totalSiswa = DataSiswa::query()->count();
        $siswaTervalidasi = DataSiswa::query()->where('status_validasi', 'validated')->count();
        $siswaLunas = DataSiswa::query()->where('status_pembayaran', 'lunas')->count();

        $totalLeads = Lead::query()->count();
        $leadsAktif = Lead::query()->whereIn('status', ['prospek', 'follow_up'])->count();
        $leadsPerluTindakLanjut = Lead::query()->where('status', 'follow_up')->count();
        $leadsBerhasil = Lead::query()->where('status', 'closing')->count();

        $projectAktif = Proyek::query()->whereIn('status_project', ['perencanaan', 'berjalan', 'tertunda'])->count();
        $totalTugas = TugasProyek::query()->count();
        $tugasTerbuka = TugasProyek::query()
            ->whereIn('status_tugas', ['belum_mulai', 'berjalan', 'review', 'tertunda'])
            ->count();
        $tugasTertunda = TugasProyek::query()->where('status_tugas', 'tertunda')->count();
        $rataProgresTugas = (int) round((float) (TugasProyek::query()->avg('persentase_progres') ?? 0));
        $tugasSelesai = TugasProyek::query()->where('status_tugas', 'selesai')->count();

        $totalArtikel = Artikel::query()->count();
        $artikelDraft = Artikel::query()->draft()->count();
        $artikelTerjadwal = Artikel::query()->terjadwal()->count();
        $artikelTerbit = Artikel::query()->terbitAktif()->count();

        $totalKursus = Kursus::query()->count();
        $totalMateri = MateriKursus::query()->count();
        $totalKuis = KuisLms::query()->count();
        $hasilKuisMasuk = HasilKuisPengguna::query()->count();

        $notifikasiBelumDibaca = $pengguna->notifikasiPengguna()->whereNull('dibaca_pada')->count();
        $totalNotifikasi = $pengguna->notifikasiPengguna()->count();
        $notifikasiDibaca = max($totalNotifikasi - $notifikasiBelumDibaca, 0);

        $tugasSaya = TugasProyek::query()->where('penanggung_jawab_id', $pengguna->id);
        $tugasSayaAktif = (clone $tugasSaya)
            ->whereIn('status_tugas', ['belum_mulai', 'berjalan', 'review', 'tertunda'])
            ->count();
        $tugasSayaSelesai = (clone $tugasSaya)->where('status_tugas', 'selesai')->count();
        $rataProgresTugasSaya = (int) round((float) ((clone $tugasSaya)->avg('persentase_progres') ?? 0));

        $progresBelajarSaya = ProgresBelajarMateri::query()->where('user_id', $pengguna->id);
        $materiSelesai = (clone $progresBelajarSaya)
            ->where(function ($query): void {
                $query->whereNotNull('selesai_pada')
                    ->orWhere('persen_progres', '>=', 100);
            })
            ->count();
        $materiBerjalan = (clone $progresBelajarSaya)
            ->whereNull('selesai_pada')
            ->where('persen_progres', '>', 0)
            ->where('persen_progres', '<', 100)
            ->count();
        $rataProgresBelajarSaya = (int) round((float) ((clone $progresBelajarSaya)->avg('persen_progres') ?? 0));

        $hasilKuisSaya = HasilKuisPengguna::query()->where('user_id', $pengguna->id);
        $kuisDikerjakan = (clone $hasilKuisSaya)->count();
        $rataSkorKuis = (int) round((float) ((clone $hasilKuisSaya)->avg('skor') ?? 0));
        $kuisLulusSaya = (clone $hasilKuisSaya)->where('lulus', true)->count();

        $linkAktif = $pengguna->linkPengguna()->where('aktif', true)->count();
        $totalKlikLink = (int) $pengguna->linkPengguna()->sum('total_klik');
        $artikelSayaDraft = Artikel::query()->where('penulis_id', $pengguna->id)->draft()->count();
        $artikelSayaTerjadwal = Artikel::query()->where('penulis_id', $pengguna->id)->terjadwal()->count();
        $artikelSayaTerbit = Artikel::query()->where('penulis_id', $pengguna->id)->terbitAktif()->count();
        $totalAktivitasSaya = $pengguna->logAktivitas()->count();

        $aktivitasTerbaru = LogAktivitas::query()
            ->with('pengguna:id,name')
            ->when(
                ! $pengguna->adalahSuperadmin(),
                fn ($query) => $query->where('user_id', $pengguna->id)
            )
            ->latest()
            ->limit(6)
            ->get();

        $prioritasTugas = TugasProyek::query()
            ->with([
                'proyek:id,nama_project',
                'penanggungJawab:id,name',
            ])
            ->whereIn('status_tugas', ['belum_mulai', 'berjalan', 'review', 'tertunda'])
            ->when(
                ! $pengguna->adalahSuperadmin(),
                fn ($query) => $query->where('penanggung_jawab_id', $pengguna->id)
            )
            ->orderByRaw('case when tanggal_target is null then 1 else 0 end')
            ->orderBy('tanggal_target')
            ->limit(5)
            ->get();

        $notifikasiTerbaru = $pengguna->notifikasiPengguna()->limit(5)->get();

        $statistik = [
            'total_produk' => $totalProduk,
            'target_omzet' => $targetOmzet,
            'target_siswa' => $targetSiswa,
            'total_siswa' => $totalSiswa,
            'siswa_tervalidasi' => $siswaTervalidasi,
            'siswa_lunas' => $siswaLunas,
            'total_leads' => $totalLeads,
            'leads_aktif' => $leadsAktif,
            'leads_perlu_tindak_lanjut' => $leadsPerluTindakLanjut,
            'leads_berhasil' => $leadsBerhasil,
            'project_aktif' => $projectAktif,
            'total_tugas' => $totalTugas,
            'tugas_terbuka' => $tugasTerbuka,
            'tugas_tertunda' => $tugasTertunda,
            'rata_progres_tugas' => $rataProgresTugas,
            'tugas_selesai' => $tugasSelesai,
            'total_artikel' => $totalArtikel,
            'artikel_draft' => $artikelDraft,
            'artikel_terjadwal' => $artikelTerjadwal,
            'artikel_terbit' => $artikelTerbit,
            'total_kursus' => $totalKursus,
            'total_materi' => $totalMateri,
            'total_kuis' => $totalKuis,
            'hasil_kuis_masuk' => $hasilKuisMasuk,
            'notifikasi_belum_dibaca' => $notifikasiBelumDibaca,
            'total_notifikasi' => $totalNotifikasi,
            'notifikasi_dibaca' => $notifikasiDibaca,
            'tugas_saya_aktif' => $tugasSayaAktif,
            'tugas_saya_selesai' => $tugasSayaSelesai,
            'rata_progres_tugas_saya' => $rataProgresTugasSaya,
            'materi_selesai' => $materiSelesai,
            'materi_berjalan' => $materiBerjalan,
            'rata_progres_belajar_saya' => $rataProgresBelajarSaya,
            'kuis_dikerjakan' => $kuisDikerjakan,
            'rata_skor_kuis' => $rataSkorKuis,
            'kuis_lulus_saya' => $kuisLulusSaya,
            'link_aktif' => $linkAktif,
            'total_klik_link' => $totalKlikLink,
            'artikel_saya_draft' => $artikelSayaDraft,
            'artikel_saya_terjadwal' => $artikelSayaTerjadwal,
            'artikel_saya_terbit' => $artikelSayaTerbit,
            'total_aktivitas_saya' => $totalAktivitasSaya,
        ];

        $levelAkses = $pengguna->level_akses ?? LevelAksesPengguna::LEVEL_5;
        $konfigurasiBeranda = $this->susunBerandaDariProfil(
            $this->profilBerandaPengguna($levelAkses),
            $statistik,
        );

        return view('dasbor.beranda', [
            'judulAktivitas' => $pengguna->adalahSuperadmin() ? 'Aktivitas Terbaru Sistem' : 'Aktivitas Terbaru Anda',
            'aktivitasTerbaru' => $aktivitasTerbaru,
            'prioritasTugas' => $prioritasTugas,
            'notifikasiTerbaru' => $notifikasiTerbaru,
            ...$konfigurasiBeranda,
        ]);
    }

    private function profilBerandaPengguna(LevelAksesPengguna $levelAkses): ProfilBerandaPengguna
    {
        if ($levelAkses === LevelAksesPengguna::SUPERADMIN) {
            return ProfilBerandaPengguna::STRATEGIS;
        }

        return PemetaanBerandaLevel::profilUntuk($levelAkses);
    }

    private function susunBerandaDariProfil(ProfilBerandaPengguna $profilBeranda, array $statistik): array
    {
        return match ($profilBeranda) {
            ProfilBerandaPengguna::STRATEGIS => $this->susunBerandaStrategis($statistik),
            ProfilBerandaPengguna::PENGAWASAN => $this->susunBerandaLevelSatu($statistik),
            ProfilBerandaPengguna::KOORDINASI => $this->susunBerandaLevelDua($statistik),
            ProfilBerandaPengguna::OPERASIONAL => $this->susunBerandaLevelTiga($statistik),
            ProfilBerandaPengguna::KONTEN => $this->susunBerandaPersonal($statistik),
            ProfilBerandaPengguna::PERSONAL => $this->susunBerandaPersonalStandar($statistik),
            ProfilBerandaPengguna::EKSEKUSI => $this->susunBerandaLevelLima($statistik),
        };
    }

    private function susunBerandaStrategis(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Pusat Kendali Strategis',
            'deskripsiBeranda' => 'Ringkasan strategis lintas modul untuk memantau omzet, siswa, leads, project, editorial, LMS, dan beban operasional aplikasi.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Target Omzet',
                    'nilai' => 'Rp '.number_format($statistik['target_omzet'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['total_produk'], 0, ',', '.').' produk · '.number_format($statistik['target_siswa'], 0, ',', '.').' target siswa',
                    'ikon' => 'omzet',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Data Siswa',
                    'nilai' => number_format($statistik['total_siswa'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.').' tervalidasi · '.number_format($statistik['siswa_lunas'], 0, ',', '.').' lunas',
                    'ikon' => 'siswa',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Leads Aktif',
                    'nilai' => number_format($statistik['leads_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['leads_berhasil'], 0, ',', '.').' berhasil · '.number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.').' tindak lanjut',
                    'ikon' => 'leads',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Project Aktif',
                    'nilai' => number_format($statistik['project_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_terbuka'], 0, ',', '.').' tugas terbuka · '.number_format($statistik['tugas_tertunda'], 0, ',', '.').' tertunda',
                    'ikon' => 'detail_tugas',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode superadmin',
                'judul' => 'Ringkasan strategis aplikasi ada di sini.',
                'deskripsi' => 'Pantau kondisi bisnis dan operasional dari satu halaman, lalu turun langsung ke modul yang membutuhkan perhatian paling cepat.',
                'kilas' => [
                    ['label' => 'Notifikasi Baru', 'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.')],
                    ['label' => 'Leads Follow Up', 'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.')],
                    ['label' => 'Artikel Draft', 'nilai' => number_format($statistik['artikel_draft'], 0, ',', '.')],
                    ['label' => 'Tugas Tertunda', 'nilai' => number_format($statistik['tugas_tertunda'], 0, ',', '.')],
                ],
                'judul_panel' => 'Sorotan hari ini',
                'fokus' => [
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.').' leads menunggu tindak lanjut lanjutan.'],
                    ['kelas_badge' => 'bg-orange', 'teks' => number_format($statistik['artikel_draft'], 0, ',', '.').' artikel masih draft dan perlu keputusan editorial.'],
                    ['kelas_badge' => 'bg-danger', 'teks' => number_format($statistik['tugas_tertunda'], 0, ',', '.').' tugas tertunda perlu perhatian manajerial.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi admin belum dibaca.'],
                ],
                'aksi' => [
                    ['label' => 'Buka Log Aktivitas', 'route' => 'administrasi.log_aktivitas.index', 'kelas' => 'btn btn-light'],
                    ['label' => 'Kelola Pengguna', 'route' => 'pengaturan.pengguna.index', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Kesehatan Operasional',
            'indikatorOperasional' => [
                $this->buatIndikatorOperasional(
                    'Validasi siswa',
                    $statistik['siswa_tervalidasi'],
                    $statistik['total_siswa'],
                    $statistik['total_siswa'] > 0
                        ? number_format($statistik['siswa_tervalidasi'], 0, ',', '.').' dari '.number_format($statistik['total_siswa'], 0, ',', '.').' data siswa sudah tervalidasi.'
                        : 'Belum ada data siswa yang masuk.'
                ),
                $this->buatIndikatorOperasional(
                    'Closing leads',
                    $statistik['leads_berhasil'],
                    $statistik['total_leads'],
                    $statistik['total_leads'] > 0
                        ? number_format($statistik['leads_berhasil'], 0, ',', '.').' dari '.number_format($statistik['total_leads'], 0, ',', '.').' leads sudah berhasil ditutup.'
                        : 'Belum ada leads yang tercatat.'
                ),
                [
                    'label' => 'Progres tugas',
                    'persentase' => $statistik['rata_progres_tugas'],
                    'keterangan' => $statistik['total_tugas'] > 0
                        ? 'Rata-rata progres seluruh tugas berada di angka '.number_format($statistik['rata_progres_tugas'], 0, ',', '.').'%.'
                        : 'Belum ada tugas project yang dibuat.',
                ],
                $this->buatIndikatorOperasional(
                    'Publikasi artikel',
                    $statistik['artikel_terbit'],
                    $statistik['total_artikel'],
                    $statistik['total_artikel'] > 0
                        ? number_format($statistik['artikel_terbit'], 0, ',', '.').' dari '.number_format($statistik['total_artikel'], 0, ',', '.').' artikel sudah aktif diterbitkan.'
                        : 'Belum ada artikel yang dibuat.'
                ),
            ],
            'judulRingkasanLms' => 'Ringkasan LMS',
            'ringkasanLms' => [
                ['label' => 'Total kursus', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Total materi', 'nilai' => number_format($statistik['total_materi'], 0, ',', '.')],
                ['label' => 'Total kuis', 'nilai' => number_format($statistik['total_kuis'], 0, ',', '.')],
                ['label' => 'Hasil kuis masuk', 'nilai' => number_format($statistik['hasil_kuis_masuk'], 0, ',', '.')],
                ['label' => 'Kuis saya', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
                ['label' => 'Materi berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Editorial & Link',
            'ringkasanSekunder' => [
                ['label' => 'Draft artikel', 'nilai' => number_format($statistik['artikel_draft'], 0, ',', '.')],
                ['label' => 'Artikel terjadwal', 'nilai' => number_format($statistik['artikel_terjadwal'], 0, ',', '.')],
                ['label' => 'Artikel terbit', 'nilai' => number_format($statistik['artikel_terbit'], 0, ',', '.')],
                ['label' => 'Link aktif saya', 'nilai' => number_format($statistik['link_aktif'], 0, ',', '.')],
                ['label' => 'Total klik link', 'nilai' => number_format($statistik['total_klik_link'], 0, ',', '.')],
                ['label' => 'Tugas selesai', 'nilai' => number_format($statistik['tugas_selesai'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Admin',
            'aksesCepat' => [
                ['judul' => 'Omzet', 'route' => 'omzet.index', 'ikon' => 'omzet'],
                ['judul' => 'Siswa', 'route' => 'siswa.index', 'ikon' => 'siswa'],
                ['judul' => 'Leads', 'route' => 'leads.index', 'ikon' => 'leads'],
                ['judul' => 'Produk', 'route' => 'produk.index', 'ikon' => 'produk'],
                ['judul' => 'Kursus', 'route' => 'lms.kursus', 'ikon' => 'kursus'],
                ['judul' => 'Project', 'route' => 'proyek.detail_tugas', 'ikon' => 'detail_tugas'],
                ['judul' => 'Artikel', 'route' => 'tools.artikel', 'ikon' => 'artikel'],
                ['judul' => 'Notifikasi', 'route' => 'notifikasi.index', 'ikon' => 'alarm'],
                ['judul' => 'Pengguna', 'route' => 'pengaturan.pengguna.index', 'ikon' => 'pengguna'],
                ['judul' => 'Log Aktivitas', 'route' => 'administrasi.log_aktivitas.index', 'ikon' => 'log_aktivitas'],
            ],
            'judulPrioritasTugas' => 'Prioritas Tugas Tim',
            'pesanKosongPrioritasTugas' => 'Tugas aktif tim akan muncul di sini untuk membantu menentukan prioritas tindak lanjut.',
        ];
    }

    private function susunBerandaLevelSatu(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Pengawasan Operasional',
            'deskripsiBeranda' => 'Ringkasan pengawasan untuk memantau validasi siswa, leads yang perlu ditindaklanjuti, tugas tertunda, dan ritme kerja tim.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Notifikasi Baru',
                    'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dibaca · '.number_format($statistik['total_notifikasi'], 0, ',', '.').' total notifikasi',
                    'ikon' => 'alarm',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Leads Tindak Lanjut',
                    'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['leads_aktif'], 0, ',', '.').' leads aktif · '.number_format($statistik['leads_berhasil'], 0, ',', '.').' berhasil',
                    'ikon' => 'leads',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Tugas Tertunda',
                    'nilai' => number_format($statistik['tugas_tertunda'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_terbuka'], 0, ',', '.').' tugas terbuka · rata progres '.number_format($statistik['rata_progres_tugas'], 0, ',', '.').'%',
                    'ikon' => 'detail_tugas',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Siswa Tervalidasi',
                    'nilai' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['total_siswa'], 0, ',', '.').' total siswa · '.number_format($statistik['siswa_lunas'], 0, ',', '.').' lunas',
                    'ikon' => 'siswa',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode pengawasan',
                'judul' => 'Pantau ritme operasional tim.',
                'deskripsi' => 'Mode ini dirancang untuk membantu Anda menjaga tempo kerja tim, memastikan follow up berjalan, dan melihat antrian prioritas tanpa terlalu teknis.',
                'kilas' => [
                    ['label' => 'Leads Aktif', 'nilai' => number_format($statistik['leads_aktif'], 0, ',', '.')],
                    ['label' => 'Tugas Terbuka', 'nilai' => number_format($statistik['tugas_terbuka'], 0, ',', '.')],
                    ['label' => 'Artikel Draft', 'nilai' => number_format($statistik['artikel_draft'], 0, ',', '.')],
                    ['label' => 'Project Aktif', 'nilai' => number_format($statistik['project_aktif'], 0, ',', '.')],
                ],
                'judul_panel' => 'Sorotan pengawas',
                'fokus' => [
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.').' leads menunggu koordinasi tindak lanjut.'],
                    ['kelas_badge' => 'bg-orange', 'teks' => number_format($statistik['tugas_tertunda'], 0, ',', '.').' tugas tertunda perlu pemecahan hambatan.'],
                    ['kelas_badge' => 'bg-success', 'teks' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.').' siswa sudah tervalidasi dari total '.number_format($statistik['total_siswa'], 0, ',', '.').' data.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi belum dibaca.'],
                ],
                'aksi' => [
                    ['label' => 'Buka Notifikasi', 'route' => 'notifikasi.index', 'kelas' => 'btn btn-light'],
                    ['label' => 'Lihat Tugas Tim', 'route' => 'proyek.detail_tugas', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Kesehatan Tim',
            'indikatorOperasional' => [
                $this->buatIndikatorOperasional(
                    'Validasi siswa',
                    $statistik['siswa_tervalidasi'],
                    $statistik['total_siswa'],
                    $statistik['total_siswa'] > 0
                        ? number_format($statistik['siswa_tervalidasi'], 0, ',', '.').' dari '.number_format($statistik['total_siswa'], 0, ',', '.').' data siswa sudah tervalidasi.'
                        : 'Belum ada data siswa.'
                ),
                $this->buatIndikatorOperasional(
                    'Closing leads',
                    $statistik['leads_berhasil'],
                    $statistik['total_leads'],
                    $statistik['total_leads'] > 0
                        ? number_format($statistik['leads_berhasil'], 0, ',', '.').' dari '.number_format($statistik['total_leads'], 0, ',', '.').' leads sudah berhasil ditutup.'
                        : 'Belum ada leads tercatat.'
                ),
                [
                    'label' => 'Progres tugas tim',
                    'persentase' => $statistik['rata_progres_tugas'],
                    'keterangan' => $statistik['total_tugas'] > 0
                        ? 'Rata-rata progres seluruh tugas berada di '.number_format($statistik['rata_progres_tugas'], 0, ',', '.').'%.'
                        : 'Belum ada tugas tim yang dibuat.',
                ],
                [
                    'label' => 'Respons notifikasi',
                    'persentase' => $this->persentaseAman($statistik['notifikasi_dibaca'], $statistik['total_notifikasi']),
                    'keterangan' => $statistik['total_notifikasi'] > 0
                        ? number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dari '.number_format($statistik['total_notifikasi'], 0, ',', '.').' notifikasi sudah dibaca.'
                        : 'Belum ada notifikasi masuk.',
                ],
            ],
            'judulRingkasanLms' => 'Ringkasan LMS',
            'ringkasanLms' => [
                ['label' => 'Total kursus', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Total materi', 'nilai' => number_format($statistik['total_materi'], 0, ',', '.')],
                ['label' => 'Total kuis', 'nilai' => number_format($statistik['total_kuis'], 0, ',', '.')],
                ['label' => 'Hasil kuis masuk', 'nilai' => number_format($statistik['hasil_kuis_masuk'], 0, ',', '.')],
                ['label' => 'Kuis saya', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
                ['label' => 'Materi berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Funnel & Pengawasan',
            'ringkasanSekunder' => [
                ['label' => 'Target omzet', 'nilai' => 'Rp '.number_format($statistik['target_omzet'], 0, ',', '.')],
                ['label' => 'Leads tindak lanjut', 'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.')],
                ['label' => 'Tugas tertunda', 'nilai' => number_format($statistik['tugas_tertunda'], 0, ',', '.')],
                ['label' => 'Project aktif', 'nilai' => number_format($statistik['project_aktif'], 0, ',', '.')],
                ['label' => 'Artikel draft', 'nilai' => number_format($statistik['artikel_draft'], 0, ',', '.')],
                ['label' => 'Aktivitas saya', 'nilai' => number_format($statistik['total_aktivitas_saya'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Pengawasan',
            'aksesCepat' => [
                ['judul' => 'Omzet', 'route' => 'omzet.index', 'ikon' => 'omzet'],
                ['judul' => 'Siswa', 'route' => 'siswa.index', 'ikon' => 'siswa'],
                ['judul' => 'Leads', 'route' => 'leads.index', 'ikon' => 'leads'],
                ['judul' => 'Progres', 'route' => 'proyek.progres', 'ikon' => 'progres'],
                ['judul' => 'Pelaporan', 'route' => 'proyek.pelaporan', 'ikon' => 'pelaporan'],
                ['judul' => 'Evaluasi', 'route' => 'proyek.evaluasi', 'ikon' => 'evaluasi'],
                ['judul' => 'Editorial', 'route' => 'tools.artikel.editorial', 'ikon' => 'artikel'],
                ['judul' => 'Link', 'route' => 'tools.link', 'ikon' => 'link'],
                ['judul' => 'Notifikasi', 'route' => 'notifikasi.index', 'ikon' => 'alarm'],
            ],
            'judulPrioritasTugas' => 'Prioritas Tugas Pengawasan',
            'pesanKosongPrioritasTugas' => 'Tugas prioritas yang perlu segera Anda pantau akan muncul di sini untuk membantu pengawasan harian.',
        ];
    }

    private function susunBerandaLevelDua(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Koordinasi Tim',
            'deskripsiBeranda' => 'Ringkasan koordinasi untuk menjaga sinkronisasi tugas, follow up leads, dan aktivitas kerja harian tim.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Tugas Tim Terbuka',
                    'nilai' => number_format($statistik['tugas_terbuka'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_tertunda'], 0, ',', '.').' tertunda · rata progres '.number_format($statistik['rata_progres_tugas'], 0, ',', '.').'%',
                    'ikon' => 'detail_tugas',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Leads Aktif',
                    'nilai' => number_format($statistik['leads_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.').' tindak lanjut · '.number_format($statistik['leads_berhasil'], 0, ',', '.').' berhasil',
                    'ikon' => 'leads',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Project Aktif',
                    'nilai' => number_format($statistik['project_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_selesai'], 0, ',', '.').' tugas selesai · '.number_format($statistik['total_tugas'], 0, ',', '.').' total tugas',
                    'ikon' => 'daftar_project',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Notifikasi Baru',
                    'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dibaca · '.number_format($statistik['total_notifikasi'], 0, ',', '.').' total notifikasi',
                    'ikon' => 'alarm',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode koordinasi',
                'judul' => 'Jaga pergerakan tim tetap sinkron.',
                'deskripsi' => 'Mode ini membantu Anda melihat pekerjaan yang sedang berjalan, antrian follow up, dan modul yang perlu segera disentuh agar arus kerja tetap rapi.',
                'kilas' => [
                    ['label' => 'Tugas Terbuka', 'nilai' => number_format($statistik['tugas_terbuka'], 0, ',', '.')],
                    ['label' => 'Leads Aktif', 'nilai' => number_format($statistik['leads_aktif'], 0, ',', '.')],
                    ['label' => 'Siswa Tervalidasi', 'nilai' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.')],
                    ['label' => 'Notifikasi Baru', 'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.')],
                ],
                'judul_panel' => 'Fokus koordinator',
                'fokus' => [
                    ['kelas_badge' => 'bg-primary', 'teks' => number_format($statistik['tugas_terbuka'], 0, ',', '.').' tugas masih terbuka di board.'],
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.').' leads menunggu tindak lanjut berikutnya.'],
                    ['kelas_badge' => 'bg-success', 'teks' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.').' siswa sudah tervalidasi.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi koordinasi belum dibaca.'],
                ],
                'aksi' => [
                    ['label' => 'Buka Board', 'route' => 'proyek.detail_tugas', 'kelas' => 'btn btn-light'],
                    ['label' => 'Buka Leads', 'route' => 'leads.index', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Progres Koordinasi',
            'indikatorOperasional' => [
                [
                    'label' => 'Progres tugas tim',
                    'persentase' => $statistik['rata_progres_tugas'],
                    'keterangan' => $statistik['total_tugas'] > 0
                        ? 'Rata-rata progres tugas tim berada di '.number_format($statistik['rata_progres_tugas'], 0, ',', '.').'%.'
                        : 'Belum ada tugas tim.',
                ],
                $this->buatIndikatorOperasional(
                    'Validasi siswa',
                    $statistik['siswa_tervalidasi'],
                    $statistik['total_siswa'],
                    $statistik['total_siswa'] > 0
                        ? number_format($statistik['siswa_tervalidasi'], 0, ',', '.').' dari '.number_format($statistik['total_siswa'], 0, ',', '.').' siswa sudah tervalidasi.'
                        : 'Belum ada data siswa.'
                ),
                [
                    'label' => 'Respons notifikasi',
                    'persentase' => $this->persentaseAman($statistik['notifikasi_dibaca'], $statistik['total_notifikasi']),
                    'keterangan' => $statistik['total_notifikasi'] > 0
                        ? number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dari '.number_format($statistik['total_notifikasi'], 0, ',', '.').' notifikasi sudah dibaca.'
                        : 'Belum ada notifikasi.',
                ],
                [
                    'label' => 'Skor kuis saya',
                    'persentase' => $statistik['rata_skor_kuis'],
                    'keterangan' => $statistik['kuis_dikerjakan'] > 0
                        ? 'Rata skor kuis Anda berada di '.number_format($statistik['rata_skor_kuis'], 0, ',', '.').'.'
                        : 'Belum ada kuis yang Anda kerjakan.',
                ],
            ],
            'judulRingkasanLms' => 'Ringkasan LMS',
            'ringkasanLms' => [
                ['label' => 'Total kursus', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Total materi', 'nilai' => number_format($statistik['total_materi'], 0, ',', '.')],
                ['label' => 'Total kuis', 'nilai' => number_format($statistik['total_kuis'], 0, ',', '.')],
                ['label' => 'Hasil kuis masuk', 'nilai' => number_format($statistik['hasil_kuis_masuk'], 0, ',', '.')],
                ['label' => 'Materi berjalan saya', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
                ['label' => 'Kuis saya', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Sinkronisasi Tim',
            'ringkasanSekunder' => [
                ['label' => 'Project aktif', 'nilai' => number_format($statistik['project_aktif'], 0, ',', '.')],
                ['label' => 'Tugas terbuka', 'nilai' => number_format($statistik['tugas_terbuka'], 0, ',', '.')],
                ['label' => 'Siswa tervalidasi', 'nilai' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.')],
                ['label' => 'Leads tindak lanjut', 'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.')],
                ['label' => 'Artikel terjadwal', 'nilai' => number_format($statistik['artikel_terjadwal'], 0, ',', '.')],
                ['label' => 'Aktivitas saya', 'nilai' => number_format($statistik['total_aktivitas_saya'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Koordinasi',
            'aksesCepat' => [
                ['judul' => 'Detail Tugas', 'route' => 'proyek.detail_tugas', 'ikon' => 'detail_tugas'],
                ['judul' => 'Penanggung Jawab', 'route' => 'proyek.penanggung_jawab', 'ikon' => 'penanggung_jawab'],
                ['judul' => 'Progres', 'route' => 'proyek.progres', 'ikon' => 'progres'],
                ['judul' => 'Pelaporan', 'route' => 'proyek.pelaporan', 'ikon' => 'pelaporan'],
                ['judul' => 'Leads', 'route' => 'leads.index', 'ikon' => 'leads'],
                ['judul' => 'Siswa', 'route' => 'siswa.index', 'ikon' => 'siswa'],
                ['judul' => 'Notifikasi', 'route' => 'notifikasi.index', 'ikon' => 'alarm'],
                ['judul' => 'Materi', 'route' => 'lms.materi', 'ikon' => 'materi'],
            ],
            'judulPrioritasTugas' => 'Tindak Lanjut Terdekat',
            'pesanKosongPrioritasTugas' => 'Tindak lanjut tugas terdekat akan muncul di sini untuk membantu koordinasi harian.',
        ];
    }

    private function susunBerandaLevelTiga(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Operasional',
            'deskripsiBeranda' => 'Ringkasan operasional untuk menjaga eksekusi tugas, tindak lanjut leads, notifikasi, dan progres belajar tetap bergerak.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Tugas Aktif Saya',
                    'nilai' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_saya_selesai'], 0, ',', '.').' selesai · rata progres '.number_format($statistik['rata_progres_tugas_saya'], 0, ',', '.').'%',
                    'ikon' => 'detail_tugas',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Notifikasi Baru',
                    'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dibaca · '.number_format($statistik['total_notifikasi'], 0, ',', '.').' total notifikasi',
                    'ikon' => 'alarm',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Leads Tindak Lanjut',
                    'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['leads_aktif'], 0, ',', '.').' aktif · '.number_format($statistik['leads_berhasil'], 0, ',', '.').' berhasil',
                    'ikon' => 'leads',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Materi Berjalan',
                    'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['materi_selesai'], 0, ',', '.').' selesai · rata belajar '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%',
                    'ikon' => 'materi',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode operasional',
                'judul' => 'Jaga eksekusi modul tetap bergerak.',
                'deskripsi' => 'Mode ini menyeimbangkan tugas pribadi dengan kebutuhan operasional, sehingga Anda tetap bisa bergerak cepat tanpa kehilangan prioritas.',
                'kilas' => [
                    ['label' => 'Tugas Aktif', 'nilai' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.')],
                    ['label' => 'Leads Follow Up', 'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.')],
                    ['label' => 'Notifikasi Baru', 'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.')],
                    ['label' => 'Materi Selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                ],
                'judul_panel' => 'Fokus operasional',
                'fokus' => [
                    ['kelas_badge' => 'bg-success', 'teks' => number_format($statistik['tugas_saya_selesai'], 0, ',', '.').' tugas Anda sudah selesai.'],
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.').' leads butuh tindak lanjut.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi belum dibaca.'],
                    ['kelas_badge' => 'bg-primary', 'teks' => number_format($statistik['link_aktif'], 0, ',', '.').' link publik aktif siap dipakai.'],
                ],
                'aksi' => [
                    ['label' => 'Lihat Tugas', 'route' => 'proyek.detail_tugas', 'kelas' => 'btn btn-light'],
                    ['label' => 'Buka Leads', 'route' => 'leads.index', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Kinerja Operasional',
            'indikatorOperasional' => [
                [
                    'label' => 'Progres tugas saya',
                    'persentase' => $statistik['rata_progres_tugas_saya'],
                    'keterangan' => $statistik['tugas_saya_aktif'] + $statistik['tugas_saya_selesai'] > 0
                        ? 'Rata-rata progres tugas Anda berada di '.number_format($statistik['rata_progres_tugas_saya'], 0, ',', '.').'%.'
                        : 'Belum ada tugas yang ditugaskan.',
                ],
                [
                    'label' => 'Progres belajar',
                    'persentase' => $statistik['rata_progres_belajar_saya'],
                    'keterangan' => $statistik['materi_selesai'] + $statistik['materi_berjalan'] > 0
                        ? 'Rata-rata progres belajar Anda berada di '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%.'
                        : 'Belum ada progres belajar tercatat.',
                ],
                [
                    'label' => 'Skor kuis saya',
                    'persentase' => $statistik['rata_skor_kuis'],
                    'keterangan' => $statistik['kuis_dikerjakan'] > 0
                        ? number_format($statistik['kuis_lulus_saya'], 0, ',', '.').' kuis lulus dari total '.number_format($statistik['kuis_dikerjakan'], 0, ',', '.').' pengerjaan.'
                        : 'Belum ada kuis yang dikerjakan.',
                ],
                [
                    'label' => 'Respons notifikasi',
                    'persentase' => $this->persentaseAman($statistik['notifikasi_dibaca'], $statistik['total_notifikasi']),
                    'keterangan' => $statistik['total_notifikasi'] > 0
                        ? number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dari '.number_format($statistik['total_notifikasi'], 0, ',', '.').' notifikasi sudah dibaca.'
                        : 'Belum ada notifikasi.',
                ],
            ],
            'judulRingkasanLms' => 'Ringkasan Belajar',
            'ringkasanLms' => [
                ['label' => 'Kursus tersedia', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Materi berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
                ['label' => 'Materi selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                ['label' => 'Kuis dikerjakan', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
                ['label' => 'Kuis lulus', 'nilai' => number_format($statistik['kuis_lulus_saya'], 0, ',', '.')],
                ['label' => 'Rata skor', 'nilai' => number_format($statistik['rata_skor_kuis'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Lapangan & Distribusi',
            'ringkasanSekunder' => [
                ['label' => 'Leads aktif global', 'nilai' => number_format($statistik['leads_aktif'], 0, ',', '.')],
                ['label' => 'Leads tindak lanjut', 'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.')],
                ['label' => 'Siswa tervalidasi', 'nilai' => number_format($statistik['siswa_tervalidasi'], 0, ',', '.')],
                ['label' => 'Link aktif saya', 'nilai' => number_format($statistik['link_aktif'], 0, ',', '.')],
                ['label' => 'Total klik link', 'nilai' => number_format($statistik['total_klik_link'], 0, ',', '.')],
                ['label' => 'Aktivitas saya', 'nilai' => number_format($statistik['total_aktivitas_saya'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Operasional',
            'aksesCepat' => [
                ['judul' => 'Detail Tugas', 'route' => 'proyek.detail_tugas', 'ikon' => 'detail_tugas'],
                ['judul' => 'Leads', 'route' => 'leads.index', 'ikon' => 'leads'],
                ['judul' => 'Siswa', 'route' => 'siswa.index', 'ikon' => 'siswa'],
                ['judul' => 'Ads/Iklan', 'route' => 'kampanye.ads_iklan', 'ikon' => 'ads_iklan'],
                ['judul' => 'Media Sosial', 'route' => 'kampanye.media_sosial', 'ikon' => 'media_sosial'],
                ['judul' => 'Notifikasi', 'route' => 'notifikasi.index', 'ikon' => 'alarm'],
                ['judul' => 'Materi', 'route' => 'lms.materi', 'ikon' => 'materi'],
                ['judul' => 'Link', 'route' => 'tools.link', 'ikon' => 'link'],
            ],
            'judulPrioritasTugas' => 'Tugas Operasional Saya',
            'pesanKosongPrioritasTugas' => 'Tugas operasional yang menjadi tanggung jawab Anda akan muncul di sini.',
        ];
    }

    private function susunBerandaLevelLima(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Eksekusi Harian',
            'deskripsiBeranda' => 'Ringkasan eksekusi yang lebih ringkas untuk membantu Anda fokus pada tugas terdekat, notifikasi penting, dan progres belajar harian.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Tugas Hari Ini',
                    'nilai' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_saya_selesai'], 0, ',', '.').' selesai · rata progres '.number_format($statistik['rata_progres_tugas_saya'], 0, ',', '.').'%',
                    'ikon' => 'detail_tugas',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Notifikasi Baru',
                    'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dibaca · '.number_format($statistik['total_notifikasi'], 0, ',', '.').' total notifikasi',
                    'ikon' => 'alarm',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Materi Aktif',
                    'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['materi_selesai'], 0, ',', '.').' selesai · rata belajar '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%',
                    'ikon' => 'materi',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Kuis Saya',
                    'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['kuis_lulus_saya'], 0, ',', '.').' lulus · rata skor '.number_format($statistik['rata_skor_kuis'], 0, ',', '.'),
                    'ikon' => 'kuis',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode eksekusi',
                'judul' => 'Kerjakan prioritas terdekat lebih dulu.',
                'deskripsi' => 'Mode ini sengaja dibuat lebih ringkas agar Anda cepat melihat apa yang harus disentuh sekarang tanpa terdistraksi angka strategis yang tidak mendesak.',
                'kilas' => [
                    ['label' => 'Tugas Aktif', 'nilai' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.')],
                    ['label' => 'Notifikasi Baru', 'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.')],
                    ['label' => 'Materi Selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                    ['label' => 'Aktivitas Saya', 'nilai' => number_format($statistik['total_aktivitas_saya'], 0, ',', '.')],
                ],
                'judul_panel' => 'Fokus hari ini',
                'fokus' => [
                    ['kelas_badge' => 'bg-primary', 'teks' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.').' tugas aktif sedang menunggu eksekusi.'],
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi perlu segera dibaca.'],
                    ['kelas_badge' => 'bg-success', 'teks' => number_format($statistik['materi_selesai'], 0, ',', '.').' materi sudah Anda selesaikan.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['link_aktif'], 0, ',', '.').' link publik aktif siap dipakai saat dibutuhkan.'],
                ],
                'aksi' => [
                    ['label' => 'Buka Tugas', 'route' => 'proyek.detail_tugas', 'kelas' => 'btn btn-light'],
                    ['label' => 'Buka Notifikasi', 'route' => 'notifikasi.index', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Disiplin Harian',
            'indikatorOperasional' => [
                [
                    'label' => 'Progres tugas saya',
                    'persentase' => $statistik['rata_progres_tugas_saya'],
                    'keterangan' => $statistik['tugas_saya_aktif'] + $statistik['tugas_saya_selesai'] > 0
                        ? 'Rata-rata progres tugas harian Anda berada di '.number_format($statistik['rata_progres_tugas_saya'], 0, ',', '.').'%.' 
                        : 'Belum ada tugas harian yang ditugaskan.',
                ],
                [
                    'label' => 'Progres belajar',
                    'persentase' => $statistik['rata_progres_belajar_saya'],
                    'keterangan' => $statistik['materi_selesai'] + $statistik['materi_berjalan'] > 0
                        ? 'Rata-rata progres belajar Anda berada di '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%.' 
                        : 'Belum ada progres belajar yang tercatat.',
                ],
                [
                    'label' => 'Skor kuis saya',
                    'persentase' => $statistik['rata_skor_kuis'],
                    'keterangan' => $statistik['kuis_dikerjakan'] > 0
                        ? number_format($statistik['kuis_lulus_saya'], 0, ',', '.').' kuis lulus dari total '.number_format($statistik['kuis_dikerjakan'], 0, ',', '.').' pengerjaan.'
                        : 'Belum ada kuis yang dikerjakan.',
                ],
                [
                    'label' => 'Respons notifikasi',
                    'persentase' => $this->persentaseAman($statistik['notifikasi_dibaca'], $statistik['total_notifikasi']),
                    'keterangan' => $statistik['total_notifikasi'] > 0
                        ? number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dari '.number_format($statistik['total_notifikasi'], 0, ',', '.').' notifikasi sudah dibaca.'
                        : 'Belum ada notifikasi yang masuk.',
                ],
            ],
            'judulRingkasanLms' => 'Ringkasan Belajar Ringkas',
            'ringkasanLms' => [
                ['label' => 'Kursus tersedia', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Materi berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
                ['label' => 'Materi selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                ['label' => 'Kuis dikerjakan', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
                ['label' => 'Kuis lulus', 'nilai' => number_format($statistik['kuis_lulus_saya'], 0, ',', '.')],
                ['label' => 'Rata skor', 'nilai' => number_format($statistik['rata_skor_kuis'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Fokus Personal',
            'ringkasanSekunder' => [
                ['label' => 'Materi selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                ['label' => 'Leads tindak lanjut', 'nilai' => number_format($statistik['leads_perlu_tindak_lanjut'], 0, ',', '.')],
                ['label' => 'Link aktif saya', 'nilai' => number_format($statistik['link_aktif'], 0, ',', '.')],
                ['label' => 'Total klik link', 'nilai' => number_format($statistik['total_klik_link'], 0, ',', '.')],
                ['label' => 'Aktivitas saya', 'nilai' => number_format($statistik['total_aktivitas_saya'], 0, ',', '.')],
                ['label' => 'Notifikasi dibaca', 'nilai' => number_format($statistik['notifikasi_dibaca'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Eksekusi',
            'aksesCepat' => [
                ['judul' => 'Detail Tugas', 'route' => 'proyek.detail_tugas', 'ikon' => 'detail_tugas'],
                ['judul' => 'Notifikasi', 'route' => 'notifikasi.index', 'ikon' => 'alarm'],
                ['judul' => 'Leads', 'route' => 'leads.index', 'ikon' => 'leads'],
                ['judul' => 'Event', 'route' => 'kampanye.event', 'ikon' => 'event'],
                ['judul' => 'Materi', 'route' => 'lms.materi', 'ikon' => 'materi'],
                ['judul' => 'Progres Belajar', 'route' => 'lms.progres_belajar', 'ikon' => 'progres_belajar'],
                ['judul' => 'Kuis', 'route' => 'lms.kuis', 'ikon' => 'kuis'],
                ['judul' => 'Link', 'route' => 'tools.link', 'ikon' => 'link'],
                ['judul' => 'Artikel', 'route' => 'tools.artikel', 'ikon' => 'artikel'],
            ],
            'judulPrioritasTugas' => 'Tugas Saya Paling Dekat',
            'pesanKosongPrioritasTugas' => 'Tugas aktif dengan target paling dekat akan muncul di sini supaya eksekusi harian tetap fokus.',
        ];
    }

    private function susunBerandaPersonalStandar(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Ruang Kerja',
            'deskripsiBeranda' => 'Ringkasan personal untuk memantau tugas, progres belajar, kuis, notifikasi, dan tools yang terkait langsung dengan akun Anda.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Notifikasi Baru',
                    'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dibaca · '.number_format($statistik['total_notifikasi'], 0, ',', '.').' total notifikasi',
                    'ikon' => 'alarm',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Tugas Aktif Saya',
                    'nilai' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['tugas_saya_selesai'], 0, ',', '.').' selesai · rata progres '.number_format($statistik['rata_progres_tugas_saya'], 0, ',', '.').'%',
                    'ikon' => 'detail_tugas',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Materi Berjalan',
                    'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['materi_selesai'], 0, ',', '.').' selesai · rata belajar '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%',
                    'ikon' => 'materi',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Kuis Saya',
                    'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['kuis_lulus_saya'], 0, ',', '.').' lulus · rata skor '.number_format($statistik['rata_skor_kuis'], 0, ',', '.'),
                    'ikon' => 'kuis',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode personal',
                'judul' => 'Fokuskan pekerjaan dan belajar Anda.',
                'deskripsi' => 'Beranda ini merangkum tugas aktif, progres belajar, hasil kuis, notifikasi, dan tools yang paling sering Anda gunakan setiap hari.',
                'kilas' => [
                    ['label' => 'Notifikasi Baru', 'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.')],
                    ['label' => 'Tugas Aktif', 'nilai' => number_format($statistik['tugas_saya_aktif'], 0, ',', '.')],
                    ['label' => 'Materi Selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                    ['label' => 'Rata Skor', 'nilai' => number_format($statistik['rata_skor_kuis'], 0, ',', '.')],
                ],
                'judul_panel' => 'Fokus saya',
                'fokus' => [
                    ['kelas_badge' => 'bg-success', 'teks' => number_format($statistik['tugas_saya_selesai'], 0, ',', '.').' tugas Anda sudah selesai.'],
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['materi_berjalan'], 0, ',', '.').' materi sedang berjalan di LMS.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi menunggu dibaca.'],
                    ['kelas_badge' => 'bg-primary', 'teks' => number_format($statistik['link_aktif'], 0, ',', '.').' link publik aktif siap dipakai.'],
                ],
                'aksi' => [
                    ['label' => 'Buka Notifikasi', 'route' => 'notifikasi.index', 'kelas' => 'btn btn-light'],
                    ['label' => 'Lihat Tugas', 'route' => 'proyek.detail_tugas', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Kinerja Pribadi',
            'indikatorOperasional' => [
                [
                    'label' => 'Progres tugas saya',
                    'persentase' => $statistik['rata_progres_tugas_saya'],
                    'keterangan' => $statistik['tugas_saya_aktif'] + $statistik['tugas_saya_selesai'] > 0
                        ? 'Rata-rata progres tugas yang Anda pegang berada di '.number_format($statistik['rata_progres_tugas_saya'], 0, ',', '.').'%.'
                        : 'Belum ada tugas yang ditugaskan kepada Anda.',
                ],
                [
                    'label' => 'Progres belajar saya',
                    'persentase' => $statistik['rata_progres_belajar_saya'],
                    'keterangan' => $statistik['materi_selesai'] + $statistik['materi_berjalan'] > 0
                        ? 'Rata-rata progres belajar Anda berada di '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%.'
                        : 'Belum ada progres belajar yang tercatat.',
                ],
                [
                    'label' => 'Skor kuis saya',
                    'persentase' => $statistik['rata_skor_kuis'],
                    'keterangan' => $statistik['kuis_dikerjakan'] > 0
                        ? number_format($statistik['kuis_lulus_saya'], 0, ',', '.').' dari '.number_format($statistik['kuis_dikerjakan'], 0, ',', '.').' kuis sudah lulus.'
                        : 'Belum ada kuis yang Anda kerjakan.',
                ],
                [
                    'label' => 'Respons notifikasi',
                    'persentase' => $this->persentaseAman($statistik['notifikasi_dibaca'], $statistik['total_notifikasi']),
                    'keterangan' => $statistik['total_notifikasi'] > 0
                        ? number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dari '.number_format($statistik['total_notifikasi'], 0, ',', '.').' notifikasi sudah dibaca.'
                        : 'Belum ada notifikasi yang masuk.',
                ],
            ],
            'judulRingkasanLms' => 'Ringkasan Belajar Saya',
            'ringkasanLms' => [
                ['label' => 'Kursus tersedia', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Materi berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
                ['label' => 'Materi selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                ['label' => 'Kuis dikerjakan', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
                ['label' => 'Kuis lulus', 'nilai' => number_format($statistik['kuis_lulus_saya'], 0, ',', '.')],
                ['label' => 'Rata skor', 'nilai' => number_format($statistik['rata_skor_kuis'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Tools Saya',
            'ringkasanSekunder' => [
                ['label' => 'Draft artikel saya', 'nilai' => number_format($statistik['artikel_saya_draft'], 0, ',', '.')],
                ['label' => 'Artikel terjadwal saya', 'nilai' => number_format($statistik['artikel_saya_terjadwal'], 0, ',', '.')],
                ['label' => 'Artikel terbit saya', 'nilai' => number_format($statistik['artikel_saya_terbit'], 0, ',', '.')],
                ['label' => 'Link aktif saya', 'nilai' => number_format($statistik['link_aktif'], 0, ',', '.')],
                ['label' => 'Total klik link', 'nilai' => number_format($statistik['total_klik_link'], 0, ',', '.')],
                ['label' => 'Aktivitas saya', 'nilai' => number_format($statistik['total_aktivitas_saya'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Kerja',
            'aksesCepat' => [
                ['judul' => 'Notifikasi', 'route' => 'notifikasi.index', 'ikon' => 'alarm'],
                ['judul' => 'Detail Tugas', 'route' => 'proyek.detail_tugas', 'ikon' => 'detail_tugas'],
                ['judul' => 'Materi', 'route' => 'lms.materi', 'ikon' => 'materi'],
                ['judul' => 'Progres Belajar', 'route' => 'lms.progres_belajar', 'ikon' => 'progres_belajar'],
                ['judul' => 'Kuis', 'route' => 'lms.kuis', 'ikon' => 'kuis'],
                ['judul' => 'Artikel', 'route' => 'tools.artikel', 'ikon' => 'artikel'],
                ['judul' => 'Link', 'route' => 'tools.link', 'ikon' => 'link'],
                ['judul' => 'Leads', 'route' => 'leads.index', 'ikon' => 'leads'],
            ],
            'judulPrioritasTugas' => 'Tugas Saya Terdekat',
            'pesanKosongPrioritasTugas' => 'Tugas aktif yang menjadi tanggung jawab Anda akan muncul di sini untuk membantu fokus kerja.',
        ];
    }

    private function susunBerandaPersonal(array $statistik): array
    {
        return [
            'pratitelBeranda' => 'Konten & Distribusi',
            'deskripsiBeranda' => 'Ringkasan kreator untuk memantau antrian artikel, distribusi link, kampanye konten, dan pengayaan pembelajaran dari satu ruang kerja.',
            'kartuRingkasan' => [
                [
                    'judul' => 'Draft Artikel',
                    'nilai' => number_format($statistik['artikel_saya_draft'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['artikel_saya_terjadwal'], 0, ',', '.').' terjadwal · '.number_format($statistik['artikel_saya_terbit'], 0, ',', '.').' terbit',
                    'ikon' => 'artikel',
                    'kelas_kartu' => 'kartu-ringkasan kartu-ringkasan-primary',
                    'kelas_avatar' => 'bg-primary-lt text-primary',
                ],
                [
                    'judul' => 'Link Aktif',
                    'nilai' => number_format($statistik['link_aktif'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['total_klik_link'], 0, ',', '.').' total klik · '.number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi baru',
                    'ikon' => 'link',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-azure-lt text-azure',
                ],
                [
                    'judul' => 'Distribusi Kampanye',
                    'nilai' => number_format($statistik['link_aktif'] + $statistik['artikel_saya_terjadwal'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['artikel_saya_terjadwal'], 0, ',', '.').' artikel siap jalan · '.number_format($statistik['artikel_saya_terbit'], 0, ',', '.').' sudah terbit',
                    'ikon' => 'media_sosial',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-orange-lt text-orange',
                ],
                [
                    'judul' => 'Pengayaan LMS',
                    'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.'),
                    'keterangan' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.').' kuis dikerjakan · rata skor '.number_format($statistik['rata_skor_kuis'], 0, ',', '.'),
                    'ikon' => 'materi',
                    'kelas_kartu' => 'kartu-ringkasan',
                    'kelas_avatar' => 'bg-green-lt text-green',
                ],
            ],
            'heroBeranda' => [
                'badge' => 'Mode konten',
                'judul' => 'Kelola konten dan distribusinya dari satu ruang kerja.',
                'deskripsi' => 'Mode ini menempatkan artikel, link publik, kanal kampanye, dan pembelajaran yang relevan dalam satu alur supaya proses produksi sampai distribusi terasa menyatu.',
                'kilas' => [
                    ['label' => 'Draft Artikel', 'nilai' => number_format($statistik['artikel_saya_draft'], 0, ',', '.')],
                    ['label' => 'Link Aktif', 'nilai' => number_format($statistik['link_aktif'], 0, ',', '.')],
                    ['label' => 'Klik Link', 'nilai' => number_format($statistik['total_klik_link'], 0, ',', '.')],
                    ['label' => 'Materi Berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
                ],
                'judul_panel' => 'Fokus kreator',
                'fokus' => [
                    ['kelas_badge' => 'bg-warning', 'teks' => number_format($statistik['artikel_saya_draft'], 0, ',', '.').' draft artikel masih menunggu penyelesaian.'],
                    ['kelas_badge' => 'bg-success', 'teks' => number_format($statistik['artikel_saya_terbit'], 0, ',', '.').' artikel sudah aktif tayang.'],
                    ['kelas_badge' => 'bg-primary', 'teks' => number_format($statistik['link_aktif'], 0, ',', '.').' link publik aktif siap didorong ke kanal distribusi.'],
                    ['kelas_badge' => 'bg-info', 'teks' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.').' notifikasi editorial atau distribusi belum dibaca.'],
                ],
                'aksi' => [
                    ['label' => 'Artikel Saya', 'route' => 'tools.artikel.saya', 'kelas' => 'btn btn-light'],
                    ['label' => 'Buka Link', 'route' => 'tools.link', 'kelas' => 'btn btn-outline-light'],
                ],
            ],
            'judulIndikator' => 'Kesiapan Konten',
            'indikatorOperasional' => [
                [
                    'label' => 'Kesiapan artikel',
                    'persentase' => $this->persentaseAman($statistik['artikel_saya_terbit'], max($statistik['artikel_saya_draft'] + $statistik['artikel_saya_terjadwal'] + $statistik['artikel_saya_terbit'], 0)),
                    'keterangan' => $statistik['artikel_saya_draft'] + $statistik['artikel_saya_terjadwal'] + $statistik['artikel_saya_terbit'] > 0
                        ? number_format($statistik['artikel_saya_terbit'], 0, ',', '.').' artikel sudah terbit dari total antrian konten Anda.'
                        : 'Belum ada artikel yang tercatat pada akun ini.',
                ],
                [
                    'label' => 'Aktivasi distribusi',
                    'persentase' => $this->persentaseAman($statistik['link_aktif'], max($statistik['link_aktif'] + $statistik['artikel_saya_terjadwal'], 0)),
                    'keterangan' => $statistik['link_aktif'] + $statistik['artikel_saya_terjadwal'] > 0
                        ? number_format($statistik['link_aktif'], 0, ',', '.').' link aktif siap dipakai dari total antrian distribusi Anda.'
                        : 'Belum ada link aktif atau artikel terjadwal.',
                ],
                [
                    'label' => 'Progres belajar saya',
                    'persentase' => $statistik['rata_progres_belajar_saya'],
                    'keterangan' => $statistik['materi_selesai'] + $statistik['materi_berjalan'] > 0
                        ? 'Rata-rata progres belajar Anda berada di '.number_format($statistik['rata_progres_belajar_saya'], 0, ',', '.').'%.'
                        : 'Belum ada progres belajar yang tercatat.',
                ],
                [
                    'label' => 'Skor kuis saya',
                    'persentase' => $statistik['rata_skor_kuis'],
                    'keterangan' => $statistik['kuis_dikerjakan'] > 0
                        ? number_format($statistik['kuis_lulus_saya'], 0, ',', '.').' dari '.number_format($statistik['kuis_dikerjakan'], 0, ',', '.').' kuis sudah lulus.'
                        : 'Belum ada kuis yang Anda kerjakan.',
                ],
                [
                    'label' => 'Respons notifikasi',
                    'persentase' => $this->persentaseAman($statistik['notifikasi_dibaca'], $statistik['total_notifikasi']),
                    'keterangan' => $statistik['total_notifikasi'] > 0
                        ? number_format($statistik['notifikasi_dibaca'], 0, ',', '.').' dari '.number_format($statistik['total_notifikasi'], 0, ',', '.').' notifikasi sudah dibaca.'
                        : 'Belum ada notifikasi yang masuk.',
                ],
            ],
            'judulRingkasanLms' => 'Pembelajaran & Pengayaan',
            'ringkasanLms' => [
                ['label' => 'Kursus tersedia', 'nilai' => number_format($statistik['total_kursus'], 0, ',', '.')],
                ['label' => 'Materi berjalan', 'nilai' => number_format($statistik['materi_berjalan'], 0, ',', '.')],
                ['label' => 'Materi selesai', 'nilai' => number_format($statistik['materi_selesai'], 0, ',', '.')],
                ['label' => 'Kuis dikerjakan', 'nilai' => number_format($statistik['kuis_dikerjakan'], 0, ',', '.')],
                ['label' => 'Kuis lulus', 'nilai' => number_format($statistik['kuis_lulus_saya'], 0, ',', '.')],
                ['label' => 'Rata skor', 'nilai' => number_format($statistik['rata_skor_kuis'], 0, ',', '.')],
            ],
            'judulRingkasanSekunder' => 'Distribusi Publik',
            'ringkasanSekunder' => [
                ['label' => 'Draft artikel saya', 'nilai' => number_format($statistik['artikel_saya_draft'], 0, ',', '.')],
                ['label' => 'Artikel terjadwal saya', 'nilai' => number_format($statistik['artikel_saya_terjadwal'], 0, ',', '.')],
                ['label' => 'Artikel terbit saya', 'nilai' => number_format($statistik['artikel_saya_terbit'], 0, ',', '.')],
                ['label' => 'Link aktif saya', 'nilai' => number_format($statistik['link_aktif'], 0, ',', '.')],
                ['label' => 'Total klik link', 'nilai' => number_format($statistik['total_klik_link'], 0, ',', '.')],
                ['label' => 'Notifikasi baru', 'nilai' => number_format($statistik['notifikasi_belum_dibaca'], 0, ',', '.')],
            ],
            'judulAksesCepat' => 'Jalur Cepat Konten',
            'aksesCepat' => [
                ['judul' => 'Artikel Saya', 'route' => 'tools.artikel.saya', 'ikon' => 'artikel'],
                ['judul' => 'Editorial', 'route' => 'tools.artikel.editorial', 'ikon' => 'artikel'],
                ['judul' => 'Link', 'route' => 'tools.link', 'ikon' => 'link'],
                ['judul' => 'Website', 'route' => 'kampanye.website', 'ikon' => 'website'],
                ['judul' => 'Media Sosial', 'route' => 'kampanye.media_sosial', 'ikon' => 'media_sosial'],
                ['judul' => 'Youtube', 'route' => 'kampanye.youtube', 'ikon' => 'youtube'],
                ['judul' => 'Materi', 'route' => 'lms.materi', 'ikon' => 'materi'],
                ['judul' => 'Kuis', 'route' => 'lms.kuis', 'ikon' => 'kuis'],
            ],
            'judulPrioritasTugas' => 'Antrian Konten Saya',
            'pesanKosongPrioritasTugas' => 'Tugas kreatif dan distribusi yang menjadi tanggung jawab Anda akan muncul di sini untuk membantu menjaga ritme produksi.',
        ];
    }

    private function prosesCta(Request $request, User $pengguna, bool $menggunakanDomainKustom): RedirectResponse
    {
        $pengguna = $this->siapkanPenggunaPublik($request, $pengguna, $menggunakanDomainKustom);
        $tujuan = $pengguna->urlCtaLinkPublik();

        abort_unless($tujuan && LinkPengguna::apakahUrlEksternalValid($tujuan), 404);

        $this->analitikLinkPublik->catat(
            $pengguna,
            StatistikLinkHarian::KLIK_CTA,
            $request,
            urlTujuan: $tujuan,
        );

        return redirect()->away($tujuan);
    }

    private function prosesBuka(
        Request $request,
        User $pengguna,
        LinkPengguna $linkPengguna,
        bool $menggunakanDomainKustom,
    ): RedirectResponse {
        $pengguna = $this->siapkanPenggunaPublik($request, $pengguna, $menggunakanDomainKustom);
        abort_if((int) $linkPengguna->user_id !== (int) $pengguna->id || ! $linkPengguna->aktif, 404);

        $tujuan = $linkPengguna->urlTujuan();

        abort_unless(LinkPengguna::apakahUrlEksternalValid($tujuan), 404);

        $linkPengguna->increment('total_klik');
        $linkPengguna->forceFill([
            'terakhir_diklik_pada' => now(),
        ])->save();
        $this->analitikLinkPublik->catat(
            $pengguna,
            StatistikLinkHarian::KLIK_LINK,
            $request,
            $linkPengguna,
            $tujuan,
        );

        return redirect()->away($tujuan);
    }

    private function penggunaDariDomainKustom(Request $request): User
    {
        $pengguna = User::resolusikanDariDomainKustomLink($request->getHost());

        abort_unless($pengguna, 404);

        return $pengguna;
    }

    private function siapkanPenggunaPublik(Request $request, User $pengguna, bool $menggunakanDomainKustom): User
    {
        if ($menggunakanDomainKustom) {
            $pengguna->tandaiDomainKustomTerhubung($request->getHost());
        }

        return $pengguna;
    }

    private function buatIndikatorOperasional(
        string $label,
        int $bagian,
        int $total,
        string $keterangan,
    ): array {
        return [
            'label' => $label,
            'persentase' => $this->persentaseAman($bagian, $total),
            'keterangan' => $keterangan,
        ];
    }

    private function persentaseAman(int $bagian, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($bagian / $total) * 100);
    }
}
