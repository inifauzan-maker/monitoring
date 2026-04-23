<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\BankSoalLms;
use App\Models\HasilKuisPengguna;
use App\Models\KuisLms;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\PercobaanKuisPengguna;
use App\Models\PertanyaanKuis;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class KuisController extends Controller
{
    private const OPSI_TARGET = [
        'kursus' => 'Kursus',
        'materi' => 'Materi',
    ];

    public function index(Request $request): View
    {
        return $this->renderHalaman($request);
    }

    public function edit(Request $request, KuisLms $kuisLms): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderHalaman($request, $kuisLms);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->validatedKuisData($request);
        $kuis = KuisLms::query()->create($data);
        $kuis->load(['kursus', 'materiKursus']);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'tambah',
            'Kuis LMS baru ditambahkan.',
            $kuis,
            $this->metadataKuis($kuis),
        );

        return redirect()
            ->route('lms.kuis', $this->filterQuery($request))
            ->with('status', 'Kuis berhasil ditambahkan.');
    }

    public function update(Request $request, KuisLms $kuisLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $snapshotSebelum = $this->metadataKuis($kuisLms->load(['kursus', 'materiKursus']));
        $data = $this->validatedKuisData($request);
        $kuisLms->update($data);
        $kuisLms->load(['kursus', 'materiKursus']);
        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'ubah',
            'Kuis LMS diperbarui.',
            $kuisLms,
            [
                'sebelum' => $snapshotSebelum,
                'sesudah' => $this->metadataKuis($kuisLms),
            ],
        );

        return redirect()
            ->route('lms.kuis', $this->filterQuery($request))
            ->with('status', 'Kuis berhasil diperbarui.');
    }

    public function destroy(Request $request, KuisLms $kuisLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $kuisLms->load(['kursus', 'materiKursus']);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'hapus',
            'Kuis LMS dihapus.',
            $kuisLms,
            $this->metadataKuis($kuisLms),
        );

        $kuisLms->delete();

        return redirect()
            ->route('lms.kuis', $this->filterQuery($request))
            ->with('status', 'Kuis berhasil dihapus.');
    }

    public function show(Request $request, KuisLms $kuisLms): View
    {
        $this->authorizeQuizAccess($request, $kuisLms);

        $kuisLms->load([
            'kursus',
            'materiKursus.kursus',
            'pertanyaan',
            'bankSoal.kursus',
            'hasilPengguna' => fn ($query) => $query->where('user_id', $request->user()->id),
        ]);

        $hasilPengguna = $kuisLms->hasilPengguna->first();
        $statusPercobaanPengguna = $this->statusPercobaanPengguna($kuisLms, $request->user()->id, $hasilPengguna);
        $riwayatPercobaan = PercobaanKuisPengguna::query()
            ->where('kuis_lms_id', $kuisLms->id)
            ->where('user_id', $request->user()->id)
            ->latest('percobaan_ke')
            ->limit(5)
            ->get();
        $percobaanTerakhir = $riwayatPercobaan->first();
        $editPertanyaan = null;
        $soalTersedia = $this->soalTersedia($kuisLms);
        $paketSoal = $statusPercobaanPengguna['batas_tercapai']
            ? collect()
            : $this->paketSoal($request, $kuisLms, $soalTersedia);
        $timerKuis = $statusPercobaanPengguna['batas_tercapai']
            ? null
            : $this->sinkronkanTimerKuis($request, $kuisLms, $paketSoal);
        $bankSoalTersedia = collect();

        if ($request->user()->adalahSuperadmin()) {
            if (! $kuisLms->menggunakanBankSoal()) {
                $idPertanyaan = (int) $request->query('pertanyaan');
                $editPertanyaan = $idPertanyaan > 0
                    ? $kuisLms->pertanyaan->firstWhere('id', $idPertanyaan)
                    : null;
            } else {
                $bankSoalTersedia = $this->bankSoalTersediaUntukKuis($kuisLms);
            }
        }

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'lihat',
            'Halaman detail kuis LMS dibuka.',
            $kuisLms,
            $this->metadataKuis($kuisLms),
        );

        return view('lms.kuis.show', [
            'kuis' => $kuisLms,
            'hasilPengguna' => $hasilPengguna,
            'editPertanyaan' => $editPertanyaan,
            'isSuperadmin' => $request->user()->adalahSuperadmin(),
            'paketSoal' => $paketSoal,
            'totalSumberSoal' => $soalTersedia->count(),
            'bankSoalTersedia' => $bankSoalTersedia,
            'riwayatPercobaan' => $riwayatPercobaan,
            'statusPercobaanPengguna' => $statusPercobaanPengguna,
            'reviewPercobaanTerakhir' => $this->reviewPercobaan($percobaanTerakhir),
            'timerKuis' => $timerKuis,
            'leaderboardKuis' => $this->leaderboardKuis($kuisLms, $request->user()->id),
            'analitikKuis' => $request->user()->adalahSuperadmin()
                ? $this->analitikKuis($kuisLms)
                : null,
        ]);
    }

    public function restart(Request $request, KuisLms $kuisLms): RedirectResponse
    {
        $this->authorizeQuizAccess($request, $kuisLms);

        $statusPercobaan = $this->statusPercobaanPengguna($kuisLms, $request->user()->id);

        if ($statusPercobaan['batas_tercapai']) {
            return redirect()
                ->route('lms.kuis.show', $kuisLms)
                ->withErrors([
                    'kuis' => 'Batas maksimal percobaan untuk kuis ini sudah tercapai.',
                ]);
        }

        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'mulai_ulang',
            'Percobaan kuis LMS dimulai ulang.',
            $kuisLms,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'durasi_menit' => $kuisLms->durasi_menit,
            ],
        );

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', 'Percobaan kuis dimulai ulang dan timer direset.');
    }

    public function storePertanyaan(Request $request, KuisLms $kuisLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $this->assertBukanBankSoal($kuisLms);

        $data = $this->validatedPertanyaanData($request, $kuisLms);
        $pertanyaan = $kuisLms->pertanyaan()->create($data);
        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'tambah',
            'Pertanyaan kuis LMS ditambahkan.',
            $pertanyaan,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'pertanyaan' => $pertanyaan->pertanyaan,
                'urutan' => $pertanyaan->urutan,
            ],
        );

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', 'Pertanyaan kuis berhasil ditambahkan.');
    }

    public function updatePertanyaan(Request $request, KuisLms $kuisLms, PertanyaanKuis $pertanyaanKuis): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $this->assertBukanBankSoal($kuisLms);
        abort_unless($pertanyaanKuis->kuis_lms_id === $kuisLms->id, 404);

        $snapshotSebelum = [
            'pertanyaan' => $pertanyaanKuis->pertanyaan,
            'opsi_jawaban' => $pertanyaanKuis->opsi_jawaban,
            'indeks_jawaban_benar' => $pertanyaanKuis->indeks_jawaban_benar,
            'urutan' => $pertanyaanKuis->urutan,
        ];

        $data = $this->validatedPertanyaanData($request, $kuisLms, $pertanyaanKuis);
        $pertanyaanKuis->update($data);
        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'ubah',
            'Pertanyaan kuis LMS diperbarui.',
            $pertanyaanKuis,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'sebelum' => $snapshotSebelum,
                'sesudah' => [
                    'pertanyaan' => $pertanyaanKuis->pertanyaan,
                    'opsi_jawaban' => $pertanyaanKuis->opsi_jawaban,
                    'indeks_jawaban_benar' => $pertanyaanKuis->indeks_jawaban_benar,
                    'urutan' => $pertanyaanKuis->urutan,
                ],
            ],
        );

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', 'Pertanyaan kuis berhasil diperbarui.');
    }

    public function destroyPertanyaan(Request $request, KuisLms $kuisLms, PertanyaanKuis $pertanyaanKuis): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $this->assertBukanBankSoal($kuisLms);
        abort_unless($pertanyaanKuis->kuis_lms_id === $kuisLms->id, 404);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'hapus',
            'Pertanyaan kuis LMS dihapus.',
            $pertanyaanKuis,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'pertanyaan' => $pertanyaanKuis->pertanyaan,
            ],
        );

        $pertanyaanKuis->delete();
        $this->lupakanPaketSoal($request, $kuisLms);

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', 'Pertanyaan kuis berhasil dihapus.');
    }

    public function storeBankSoal(Request $request, KuisLms $kuisLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $this->assertGunakanBankSoal($kuisLms);

        $validated = $request->validate([
            'bank_soal_lms_id' => ['required', 'integer', 'exists:bank_soal_lms,id'],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ], [], [
            'bank_soal_lms_id' => 'bank soal',
            'urutan' => 'urutan',
        ]);

        $bankSoal = BankSoalLms::query()->findOrFail((int) $validated['bank_soal_lms_id']);
        $this->assertBankSoalKompatibel($kuisLms, $bankSoal);

        $urutan = (int) ($validated['urutan'] ?? $this->urutanBankSoalDefault($kuisLms, $bankSoal->id));
        $sudahAda = $kuisLms->bankSoal()->where('bank_soal_lms.id', $bankSoal->id)->exists();

        if ($sudahAda) {
            $kuisLms->bankSoal()->updateExistingPivot($bankSoal->id, ['urutan' => $urutan]);
        } else {
            $kuisLms->bankSoal()->attach($bankSoal->id, ['urutan' => $urutan]);
        }

        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'ubah',
            'Bank soal ditempelkan ke kuis LMS.',
            $kuisLms,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'bank_soal_id' => $bankSoal->id,
                'pertanyaan' => $bankSoal->pertanyaan,
                'urutan' => $urutan,
                'mode' => $sudahAda ? 'update_urutan' : 'attach',
            ],
        );

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', $sudahAda
                ? 'Urutan bank soal pada kuis berhasil diperbarui.'
                : 'Bank soal berhasil ditempelkan ke kuis.');
    }

    public function destroyBankSoal(Request $request, KuisLms $kuisLms, BankSoalLms $bankSoalLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $this->assertGunakanBankSoal($kuisLms);

        $terpasang = $kuisLms->bankSoal()->where('bank_soal_lms.id', $bankSoalLms->id)->exists();
        abort_unless($terpasang, 404);

        $kuisLms->bankSoal()->detach($bankSoalLms->id);
        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'ubah',
            'Bank soal dilepas dari kuis LMS.',
            $kuisLms,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'bank_soal_id' => $bankSoalLms->id,
                'pertanyaan' => $bankSoalLms->pertanyaan,
                'mode' => 'detach',
            ],
        );

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', 'Bank soal berhasil dilepas dari kuis.');
    }

    public function submit(Request $request, KuisLms $kuisLms): RedirectResponse
    {
        $this->authorizeQuizAccess($request, $kuisLms);
        $kuisLms->load(['pertanyaan', 'bankSoal']);

        $soalTersedia = $this->soalTersedia($kuisLms);
        $paketSoal = $this->paketSoal($request, $kuisLms, $soalTersedia);
        $timerKuis = $this->sinkronkanTimerKuis($request, $kuisLms, $paketSoal);

        if ($paketSoal->isEmpty()) {
            throw ValidationException::withMessages([
                'kuis' => 'Kuis belum memiliki soal yang bisa dikerjakan.',
            ]);
        }

        $statusPercobaan = $this->statusPercobaanPengguna($kuisLms, $request->user()->id);

        if ($statusPercobaan['batas_tercapai']) {
            throw ValidationException::withMessages([
                'kuis' => 'Batas maksimal percobaan untuk kuis ini sudah tercapai.',
            ]);
        }

        $rules = [];
        $attributes = [];
        $submitOtomatis = $request->boolean('submit_otomatis');
        $timerHabis = (bool) ($timerKuis['sudah_habis'] ?? false);
        $izinkanJawabanKosong = $submitOtomatis || $timerHabis;

        foreach ($paketSoal as $item) {
            $rules['jawaban.'.$item['kode']] = [$izinkanJawabanKosong ? 'nullable' : 'required', 'integer', 'between:0,3'];
            $attributes['jawaban.'.$item['kode']] = 'jawaban untuk pertanyaan #'.$item['urutan_tampil'];
        }

        $validated = $request->validate($rules, [], $attributes);
        $jawabanPengguna = collect($validated['jawaban'] ?? [])
            ->filter(fn ($nilai) => $nilai !== null && $nilai !== '')
            ->map(fn ($nilai) => (int) $nilai)
            ->all();

        $jumlahBenar = $paketSoal->filter(function (array $item) use ($jawabanPengguna) {
            return ($jawabanPengguna[$item['kode']] ?? -1) === $item['indeks_jawaban_benar_tampil'];
        })->count();

        $jumlahPertanyaan = $paketSoal->count();
        $skor = $jumlahPertanyaan > 0
            ? (int) round(($jumlahBenar / $jumlahPertanyaan) * 100)
            : 0;
        $percobaanKe = (int) $statusPercobaan['percobaan_berikutnya'];

        PercobaanKuisPengguna::query()->create([
            'kuis_lms_id' => $kuisLms->id,
            'user_id' => $request->user()->id,
            'percobaan_ke' => $percobaanKe,
            'paket_soal' => $paketSoal->map(function (array $item) {
                return [
                    'kode' => $item['kode'],
                    'label_sumber' => $item['label_sumber'],
                    'tingkat_kesulitan' => $item['tingkat_kesulitan'],
                    'label_tingkat_kesulitan' => $item['label_tingkat_kesulitan'],
                    'pertanyaan' => $item['pertanyaan'],
                    'opsi_jawaban' => $item['opsi_jawaban'],
                    'indeks_jawaban_benar_tampil' => $item['indeks_jawaban_benar_tampil'],
                ];
            })->values()->all(),
            'jawaban_pengguna' => $jawabanPengguna,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_pertanyaan' => $jumlahPertanyaan,
            'skor' => $skor,
            'lulus' => $skor >= (int) $kuisLms->nilai_lulus,
            'selesai_pada' => now(),
        ]);

        $hasil = HasilKuisPengguna::query()->firstOrNew([
            'kuis_lms_id' => $kuisLms->id,
            'user_id' => $request->user()->id,
        ]);

        $hasil->jawaban_pengguna = $jawabanPengguna;
        $hasil->jumlah_benar = $jumlahBenar;
        $hasil->jumlah_pertanyaan = $jumlahPertanyaan;
        $hasil->skor = $skor;
        $hasil->lulus = $skor >= (int) $kuisLms->nilai_lulus;
        $hasil->jumlah_percobaan = $percobaanKe;
        $hasil->selesai_pada = now();
        $hasil->save();

        $this->lupakanPaketSoal($request, $kuisLms);

        PencatatLogAktivitas::catat(
            $request,
            'kuis',
            'submit',
            'Kuis LMS dikerjakan.',
            $hasil,
            [
                'kuis_id' => $kuisLms->id,
                'kuis' => $kuisLms->judul,
                'skor' => $hasil->skor,
                'jumlah_benar' => $hasil->jumlah_benar,
                'jumlah_pertanyaan' => $hasil->jumlah_pertanyaan,
                'lulus' => $hasil->lulus,
                'jumlah_percobaan' => $hasil->jumlah_percobaan,
                'gunakan_bank_soal' => $kuisLms->menggunakanBankSoal(),
                'acak_soal' => $kuisLms->mengacakSoal(),
                'acak_opsi_jawaban' => $kuisLms->mengacakOpsiJawaban(),
                'durasi_menit' => $kuisLms->durasi_menit,
                'timer_habis' => $timerHabis,
                'submit_otomatis' => $submitOtomatis,
            ],
        );

        return redirect()
            ->route('lms.kuis.show', $kuisLms)
            ->with('status', $timerHabis
                ? ($hasil->lulus
                    ? 'Waktu kuis habis. Jawaban yang sudah masuk disimpan dan Anda lulus.'
                    : 'Waktu kuis habis. Jawaban yang sudah masuk disimpan, tetapi nilai belum melewati batas lulus.')
                : ($hasil->lulus
                    ? 'Kuis selesai dikerjakan dan Anda lulus.'
                    : 'Kuis selesai dikerjakan, tetapi nilai belum melewati batas lulus.'));
    }

    private function renderHalaman(Request $request, ?KuisLms $editItem = null): View
    {
        $targetTipe = $this->selectedTargetTipe($request->query('target_tipe'));
        $kursusDipilih = $this->selectedKursus($request->query('kursus'));
        $materiDipilih = $this->selectedMateri($request->query('materi'));
        $kataKunci = $this->selectedKataKunci($request->query('q'));

        return view('lms.kuis.index', [
            'items' => $this->queryItems($request, $targetTipe, $kursusDipilih?->id, $materiDipilih?->id, $kataKunci)->get(),
            'opsiTarget' => self::OPSI_TARGET,
            'opsiKursus' => Kursus::query()->orderBy('judul')->get(['id', 'judul']),
            'opsiMateri' => MateriKursus::query()
                ->with('kursus')
                ->when($kursusDipilih, fn ($query) => $query->where('kursus_id', $kursusDipilih->id))
                ->orderBy('kursus_id')
                ->orderBy('urutan')
                ->get(),
            'targetTipe' => $targetTipe,
            'kursusDipilih' => $kursusDipilih,
            'materiDipilih' => $materiDipilih,
            'kataKunci' => $kataKunci,
            'editItem' => $editItem?->load(['kursus', 'materiKursus']),
            'isSuperadmin' => $request->user()->adalahSuperadmin(),
        ]);
    }

    private function queryItems(Request $request, ?string $targetTipe, ?int $kursusId, ?int $materiId, ?string $kataKunci)
    {
        return KuisLms::query()
            ->with(['kursus', 'materiKursus.kursus'])
            ->withCount(['pertanyaan', 'bankSoal'])
            ->with([
                'hasilPengguna' => fn ($query) => $query->where('user_id', $request->user()->id),
            ])
            ->when(! $request->user()->adalahSuperadmin(), fn ($query) => $query->where('aktif', true))
            ->when($targetTipe, fn ($query) => $query->where('target_tipe', $targetTipe))
            ->when($kursusId, fn ($query) => $query->where('kursus_id', $kursusId))
            ->when($materiId, fn ($query) => $query->where('materi_kursus_id', $materiId))
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($builder) use ($kataKunci) {
                    $builder->where('judul', 'like', '%'.$kataKunci.'%')
                        ->orWhere('deskripsi', 'like', '%'.$kataKunci.'%')
                        ->orWhereHas('kursus', fn ($relasi) => $relasi->where('judul', 'like', '%'.$kataKunci.'%'))
                        ->orWhereHas('materiKursus', fn ($relasi) => $relasi->where('judul', 'like', '%'.$kataKunci.'%'));
                });
            })
            ->latest();
    }

    private function selectedTargetTipe(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return array_key_exists($nilai, self::OPSI_TARGET) ? $nilai : null;
    }

    private function selectedKursus(mixed $nilai): ?Kursus
    {
        $nilai = (int) $nilai;

        return $nilai > 0 ? Kursus::query()->find($nilai) : null;
    }

    private function selectedMateri(mixed $nilai): ?MateriKursus
    {
        $nilai = (int) $nilai;

        return $nilai > 0
            ? MateriKursus::query()->with('kursus')->find($nilai)
            : null;
    }

    private function selectedKataKunci(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return $nilai !== '' ? $nilai : null;
    }

    private function validatedKuisData(Request $request): array
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'target_tipe' => ['required', 'in:kursus,materi'],
            'kursus_id' => ['nullable', 'integer', 'exists:kursus,id'],
            'materi_kursus_id' => ['nullable', 'integer', 'exists:materi_kursus,id'],
            'nilai_lulus' => ['nullable', 'integer', 'min:0', 'max:100'],
            'durasi_menit' => ['nullable', 'integer', 'min:1', 'max:720'],
            'maksimal_percobaan' => ['nullable', 'integer', 'min:1', 'max:100'],
            'gunakan_bank_soal' => ['nullable', 'boolean'],
            'acak_soal' => ['nullable', 'boolean'],
            'acak_opsi_jawaban' => ['nullable', 'boolean'],
            'jumlah_soal_tampil' => ['nullable', 'integer', 'min:1', 'max:500'],
            'aktif' => ['nullable', 'boolean'],
        ], [], [
            'judul' => 'judul kuis',
            'deskripsi' => 'deskripsi',
            'target_tipe' => 'target kuis',
            'kursus_id' => 'kursus',
            'materi_kursus_id' => 'materi',
            'nilai_lulus' => 'nilai lulus',
            'durasi_menit' => 'durasi timer',
            'maksimal_percobaan' => 'maksimal percobaan',
            'gunakan_bank_soal' => 'mode bank soal',
            'acak_soal' => 'randomisasi soal',
            'acak_opsi_jawaban' => 'acak opsi jawaban',
            'jumlah_soal_tampil' => 'jumlah soal tampil',
            'aktif' => 'status aktif',
        ]);

        $validated['judul'] = trim((string) $validated['judul']);
        $validated['deskripsi'] = filled($validated['deskripsi'] ?? null)
            ? trim((string) $validated['deskripsi'])
            : null;
        $validated['nilai_lulus'] = (int) ($validated['nilai_lulus'] ?? 70);
        $validated['durasi_menit'] = filled($validated['durasi_menit'] ?? null)
            ? (int) $validated['durasi_menit']
            : null;
        $validated['maksimal_percobaan'] = filled($validated['maksimal_percobaan'] ?? null)
            ? (int) $validated['maksimal_percobaan']
            : null;
        $validated['gunakan_bank_soal'] = $request->boolean('gunakan_bank_soal');
        $validated['acak_soal'] = $request->boolean('acak_soal');
        $validated['acak_opsi_jawaban'] = $request->boolean('acak_opsi_jawaban');
        $validated['jumlah_soal_tampil'] = filled($validated['jumlah_soal_tampil'] ?? null)
            ? (int) $validated['jumlah_soal_tampil']
            : null;
        $validated['aktif'] = $request->boolean('aktif');

        if ($validated['target_tipe'] === 'kursus') {
            if (! filled($validated['kursus_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'kursus_id' => 'Pilih kursus untuk kuis ini.',
                ]);
            }

            $validated['kursus_id'] = (int) $validated['kursus_id'];
            $validated['materi_kursus_id'] = null;

            return $validated;
        }

        if (! filled($validated['materi_kursus_id'] ?? null)) {
            throw ValidationException::withMessages([
                'materi_kursus_id' => 'Pilih materi untuk kuis ini.',
            ]);
        }

        $materi = MateriKursus::query()->findOrFail((int) $validated['materi_kursus_id']);
        $validated['materi_kursus_id'] = $materi->id;
        $validated['kursus_id'] = $materi->kursus_id;

        return $validated;
    }

    private function validatedPertanyaanData(Request $request, KuisLms $kuisLms, ?PertanyaanKuis $pertanyaanKuis = null): array
    {
        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:2000'],
            'opsi_1' => ['required', 'string', 'max:255'],
            'opsi_2' => ['required', 'string', 'max:255'],
            'opsi_3' => ['required', 'string', 'max:255'],
            'opsi_4' => ['required', 'string', 'max:255'],
            'indeks_jawaban_benar' => ['required', 'integer', 'between:0,3'],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ], [], [
            'pertanyaan' => 'pertanyaan',
            'opsi_1' => 'opsi jawaban 1',
            'opsi_2' => 'opsi jawaban 2',
            'opsi_3' => 'opsi jawaban 3',
            'opsi_4' => 'opsi jawaban 4',
            'indeks_jawaban_benar' => 'jawaban benar',
            'urutan' => 'urutan',
        ]);

        return [
            'kuis_lms_id' => $kuisLms->id,
            'pertanyaan' => trim((string) $validated['pertanyaan']),
            'opsi_jawaban' => [
                trim((string) $validated['opsi_1']),
                trim((string) $validated['opsi_2']),
                trim((string) $validated['opsi_3']),
                trim((string) $validated['opsi_4']),
            ],
            'indeks_jawaban_benar' => (int) $validated['indeks_jawaban_benar'],
            'urutan' => (int) ($validated['urutan'] ?? $this->urutanPertanyaanDefault($kuisLms, $pertanyaanKuis?->id)),
        ];
    }

    private function urutanPertanyaanDefault(KuisLms $kuisLms, ?int $ignoreId = null): int
    {
        return (int) PertanyaanKuis::query()
            ->where('kuis_lms_id', $kuisLms->id)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->max('urutan') + 1;
    }

    private function urutanBankSoalDefault(KuisLms $kuisLms, ?int $ignoreId = null): int
    {
        return (int) $kuisLms->bankSoal()
            ->when($ignoreId, fn ($query) => $query->where('bank_soal_lms.id', '!=', $ignoreId))
            ->max('bank_soal_kuis_lms.urutan') + 1;
    }

    private function soalTersedia(KuisLms $kuisLms): Collection
    {
        if ($kuisLms->menggunakanBankSoal()) {
            return $kuisLms->bankSoal
                ->filter(fn (BankSoalLms $item) => $item->aktif)
                ->map(function (BankSoalLms $item) {
                    return [
                        'kode' => 'bank_'.$item->id,
                        'sumber_tipe' => 'bank_soal',
                        'sumber_id' => $item->id,
                        'urutan' => (int) ($item->pivot->urutan ?? 0),
                        'pertanyaan' => $item->pertanyaan,
                        'opsi_jawaban' => $item->opsi_jawaban,
                        'indeks_jawaban_benar' => $item->indeks_jawaban_benar,
                        'tingkat_kesulitan' => $item->tingkat_kesulitan,
                        'label_tingkat_kesulitan' => $item->labelTingkatKesulitan(),
                        'label_sumber' => 'Bank Soal',
                    ];
                })
                ->sortBy('urutan')
                ->values();
        }

        return $kuisLms->pertanyaan
            ->map(function (PertanyaanKuis $item) {
                return [
                    'kode' => 'pertanyaan_'.$item->id,
                    'sumber_tipe' => 'pertanyaan_kuis',
                    'sumber_id' => $item->id,
                    'urutan' => $item->urutan,
                    'pertanyaan' => $item->pertanyaan,
                    'opsi_jawaban' => $item->opsi_jawaban,
                    'indeks_jawaban_benar' => $item->indeks_jawaban_benar,
                    'tingkat_kesulitan' => null,
                    'label_tingkat_kesulitan' => 'Manual',
                    'label_sumber' => 'Manual',
                ];
            })
            ->sortBy('urutan')
            ->values();
    }

    private function paketSoal(Request $request, KuisLms $kuisLms, Collection $soalTersedia): Collection
    {
        if ($soalTersedia->isEmpty()) {
            return collect();
        }

        $jumlahTampil = $kuisLms->jumlahSoalTampilEfektif();
        $sessionKey = $this->sessionKeyPaketSoal($request, $kuisLms);
        $soalByKode = $soalTersedia->keyBy('kode');

        $paketTersimpan = collect($request->session()->get($sessionKey, []))
            ->filter(function ($item) use ($soalByKode) {
                return is_array($item)
                    && filled($item['kode'] ?? null)
                    && $soalByKode->has((string) $item['kode']);
            })
            ->values();

        if ($paketTersimpan->count() === $jumlahTampil) {
            return $paketTersimpan->map(function (array $item, int $index) {
                $item['urutan_tampil'] = $index + 1;

                return $item;
            })->values();
        }

        $urutanAwal = $kuisLms->mengacakSoal()
            ? $soalTersedia->shuffle()->values()
            : $soalTersedia->sortBy('urutan')->values();

        $paketBaru = $urutanAwal
            ->take($jumlahTampil)
            ->values()
            ->map(function (array $item, int $index) use ($kuisLms) {
                return $this->bangunPaketSoalItem($item, $index + 1, $kuisLms->mengacakOpsiJawaban());
            })
            ->values();

        $request->session()->put($sessionKey, $paketBaru->all());

        return $paketBaru;
    }

    private function bankSoalTersediaUntukKuis(KuisLms $kuisLms): Collection
    {
        $terpasang = $kuisLms->bankSoal->pluck('id')->all();

        return BankSoalLms::query()
            ->with('kursus')
            ->where('aktif', true)
            ->when($kuisLms->kursus_id, function ($query) use ($kuisLms) {
                $query->where(function ($builder) use ($kuisLms) {
                    $builder->whereNull('kursus_id')
                        ->orWhere('kursus_id', $kuisLms->kursus_id);
                });
            })
            ->when($terpasang !== [], fn ($query) => $query->whereNotIn('id', $terpasang))
            ->orderBy('kursus_id')
            ->orderByDesc('id')
            ->get();
    }

    private function metadataKuis(KuisLms $kuis): array
    {
        return [
            'judul' => $kuis->judul,
            'target_tipe' => $kuis->target_tipe,
            'kursus_id' => $kuis->kursus_id,
            'kursus' => $kuis->kursus?->judul,
            'materi_kursus_id' => $kuis->materi_kursus_id,
            'materi' => $kuis->materiKursus?->judul,
            'nilai_lulus' => $kuis->nilai_lulus,
            'durasi_menit' => $kuis->durasi_menit,
            'maksimal_percobaan' => $kuis->maksimal_percobaan,
            'gunakan_bank_soal' => $kuis->gunakan_bank_soal,
            'acak_soal' => $kuis->acak_soal,
            'acak_opsi_jawaban' => $kuis->acak_opsi_jawaban,
            'jumlah_soal_tampil' => $kuis->jumlah_soal_tampil,
            'aktif' => $kuis->aktif,
        ];
    }

    private function bangunPaketSoalItem(array $item, int $urutanTampil, bool $acakOpsiJawaban): array
    {
        $opsiAsli = collect($item['opsi_jawaban'] ?? [])
            ->values()
            ->map(fn ($label, int $indeks) => [
                'indeks_asli' => $indeks,
                'label' => $label,
            ]);

        $opsiTampil = $acakOpsiJawaban
            ? $opsiAsli->shuffle()->values()
            : $opsiAsli->values();

        $indeksJawabanBenarTampil = (int) $opsiTampil
            ->search(fn (array $opsi) => $opsi['indeks_asli'] === (int) $item['indeks_jawaban_benar']);

        return [
            'kode' => $item['kode'],
            'sumber_tipe' => $item['sumber_tipe'],
            'sumber_id' => $item['sumber_id'],
            'urutan' => $item['urutan'],
            'urutan_tampil' => $urutanTampil,
            'pertanyaan' => $item['pertanyaan'],
            'opsi_jawaban' => $opsiTampil->pluck('label')->values()->all(),
            'indeks_jawaban_benar_tampil' => $indeksJawabanBenarTampil,
            'tingkat_kesulitan' => $item['tingkat_kesulitan'],
            'label_tingkat_kesulitan' => $item['label_tingkat_kesulitan'],
            'label_sumber' => $item['label_sumber'],
        ];
    }

    private function reviewPercobaan(?PercobaanKuisPengguna $percobaan): Collection
    {
        if (! $percobaan) {
            return collect();
        }

        $jawabanPengguna = collect($percobaan->jawaban_pengguna ?? []);

        return collect($percobaan->paket_soal ?? [])
            ->values()
            ->map(function (array $item, int $index) use ($jawabanPengguna) {
                $indeksJawabanPengguna = $jawabanPengguna->has($item['kode'] ?? null)
                    ? (int) $jawabanPengguna->get($item['kode'])
                    : null;
                $indeksJawabanBenar = array_key_exists('indeks_jawaban_benar_tampil', $item)
                    ? (int) $item['indeks_jawaban_benar_tampil']
                    : null;

                return [
                    'urutan_tampil' => $index + 1,
                    'pertanyaan' => $item['pertanyaan'] ?? '-',
                    'opsi_jawaban' => $item['opsi_jawaban'] ?? [],
                    'label_sumber' => $item['label_sumber'] ?? '-',
                    'label_tingkat_kesulitan' => $item['label_tingkat_kesulitan'] ?? 'Manual',
                    'indeks_jawaban_pengguna' => $indeksJawabanPengguna,
                    'indeks_jawaban_benar' => $indeksJawabanBenar,
                    'jawaban_pengguna' => is_int($indeksJawabanPengguna) ? ($item['opsi_jawaban'][$indeksJawabanPengguna] ?? 'Tidak dijawab') : 'Tidak dijawab',
                    'jawaban_benar' => is_int($indeksJawabanBenar) ? ($item['opsi_jawaban'][$indeksJawabanBenar] ?? '-') : '-',
                    'benar' => is_int($indeksJawabanPengguna) && is_int($indeksJawabanBenar)
                        ? $indeksJawabanPengguna === $indeksJawabanBenar
                        : false,
                ];
            });
    }

    private function analitikKuis(KuisLms $kuisLms): array
    {
        $percobaan = PercobaanKuisPengguna::query()
            ->where('kuis_lms_id', $kuisLms->id)
            ->orderByDesc('percobaan_ke')
            ->get();

        $totalPercobaan = $percobaan->count();
        $rataRataSkor = $totalPercobaan > 0
            ? (int) round($percobaan->avg('skor'))
            : 0;
        $tingkatLulus = $totalPercobaan > 0
            ? (int) round(($percobaan->where('lulus', true)->count() / $totalPercobaan) * 100)
            : 0;

        $detailKesulitan = $percobaan
            ->flatMap(function (PercobaanKuisPengguna $item) {
                $jawaban = collect($item->jawaban_pengguna ?? []);

                return collect($item->paket_soal ?? [])->map(function (array $paket) use ($jawaban) {
                    $kode = (string) ($paket['kode'] ?? '');
                    $indeksJawabanPengguna = $jawaban->has($kode) ? (int) $jawaban->get($kode) : null;
                    $indeksBenar = array_key_exists('indeks_jawaban_benar_tampil', $paket)
                        ? (int) $paket['indeks_jawaban_benar_tampil']
                        : null;
                    $kesulitan = $paket['tingkat_kesulitan'] ?? 'manual';

                    return [
                        'tingkat_kesulitan' => $kesulitan,
                        'label_tingkat_kesulitan' => $paket['label_tingkat_kesulitan'] ?? 'Manual',
                        'benar' => is_int($indeksJawabanPengguna) && is_int($indeksBenar)
                            ? $indeksJawabanPengguna === $indeksBenar
                            : false,
                    ];
                });
            })
            ->groupBy('tingkat_kesulitan')
            ->map(function (Collection $items) {
                $total = $items->count();
                $jumlahBenar = $items->where('benar', true)->count();

                return [
                    'label' => $items->first()['label_tingkat_kesulitan'] ?? 'Manual',
                    'total' => $total,
                    'benar' => $jumlahBenar,
                    'akurasi' => $total > 0 ? (int) round(($jumlahBenar / $total) * 100) : 0,
                ];
            })
            ->sortBy('label')
            ->values();

        return [
            'total_percobaan' => $totalPercobaan,
            'rata_rata_skor' => $rataRataSkor,
            'tingkat_lulus' => $tingkatLulus,
            'detail_kesulitan' => $detailKesulitan,
        ];
    }

    private function statusPercobaanPengguna(KuisLms $kuisLms, int $userId, ?HasilKuisPengguna $hasilPengguna = null): array
    {
        $jumlahPercobaan = $hasilPengguna?->jumlah_percobaan;

        if (! is_int($jumlahPercobaan)) {
            $jumlahPercobaan = (int) PercobaanKuisPengguna::query()
                ->where('kuis_lms_id', $kuisLms->id)
                ->where('user_id', $userId)
                ->max('percobaan_ke');
        }

        $maksimalPercobaan = $kuisLms->membatasiPercobaan()
            ? (int) $kuisLms->maksimal_percobaan
            : null;
        $batasTercapai = $maksimalPercobaan !== null && $jumlahPercobaan >= $maksimalPercobaan;

        return [
            'jumlah' => max(0, (int) $jumlahPercobaan),
            'maksimal' => $maksimalPercobaan,
            'sisa' => $maksimalPercobaan !== null
                ? max(0, $maksimalPercobaan - (int) $jumlahPercobaan)
                : null,
            'batas_tercapai' => $batasTercapai,
            'percobaan_berikutnya' => max(0, (int) $jumlahPercobaan) + 1,
        ];
    }

    private function leaderboardKuis(KuisLms $kuisLms, int $userId): array
    {
        $peringkatPengguna = PercobaanKuisPengguna::query()
            ->with('pengguna:id,name')
            ->where('kuis_lms_id', $kuisLms->id)
            ->get()
            ->groupBy('user_id')
            ->map(function (Collection $items) {
                /** @var \App\Models\PercobaanKuisPengguna $percobaanTerbaik */
                $percobaanTerbaik = $items
                    ->sort(function (PercobaanKuisPengguna $a, PercobaanKuisPengguna $b) {
                        if ($a->skor !== $b->skor) {
                            return $b->skor <=> $a->skor;
                        }

                        if ($a->jumlah_benar !== $b->jumlah_benar) {
                            return $b->jumlah_benar <=> $a->jumlah_benar;
                        }

                        if ($a->percobaan_ke !== $b->percobaan_ke) {
                            return $a->percobaan_ke <=> $b->percobaan_ke;
                        }

                        return ($a->selesai_pada?->getTimestamp() ?? PHP_INT_MAX)
                            <=> ($b->selesai_pada?->getTimestamp() ?? PHP_INT_MAX);
                    })
                    ->first();

                return [
                    'user_id' => $percobaanTerbaik->user_id,
                    'nama' => $percobaanTerbaik->pengguna?->name ?? 'Pengguna',
                    'skor' => (int) $percobaanTerbaik->skor,
                    'jumlah_benar' => (int) $percobaanTerbaik->jumlah_benar,
                    'jumlah_pertanyaan' => (int) $percobaanTerbaik->jumlah_pertanyaan,
                    'percobaan_terbaik' => (int) $percobaanTerbaik->percobaan_ke,
                    'total_percobaan' => $items->count(),
                    'selesai_pada' => $percobaanTerbaik->selesai_pada,
                ];
            })
            ->sort(function (array $a, array $b) {
                if ($a['skor'] !== $b['skor']) {
                    return $b['skor'] <=> $a['skor'];
                }

                if ($a['jumlah_benar'] !== $b['jumlah_benar']) {
                    return $b['jumlah_benar'] <=> $a['jumlah_benar'];
                }

                if ($a['total_percobaan'] !== $b['total_percobaan']) {
                    return $a['total_percobaan'] <=> $b['total_percobaan'];
                }

                return ($a['selesai_pada']?->getTimestamp() ?? PHP_INT_MAX)
                    <=> ($b['selesai_pada']?->getTimestamp() ?? PHP_INT_MAX);
            })
            ->values()
            ->map(function (array $item, int $index) use ($userId) {
                $item['peringkat'] = $index + 1;
                $item['adalah_pengguna_aktif'] = (int) $item['user_id'] === $userId;

                return $item;
            });

        return [
            'total_peserta' => $peringkatPengguna->count(),
            'items' => $peringkatPengguna->take(10),
            'peringkat_pengguna' => $peringkatPengguna->firstWhere('user_id', $userId),
        ];
    }

    private function sessionKeyPaketSoal(Request $request, KuisLms $kuisLms): string
    {
        return 'lms.kuis.paket.'.$request->user()->id.'.'.$kuisLms->id;
    }

    private function sessionKeyTimerKuis(Request $request, KuisLms $kuisLms): string
    {
        return 'lms.kuis.timer.'.$request->user()->id.'.'.$kuisLms->id;
    }

    private function sinkronkanTimerKuis(Request $request, KuisLms $kuisLms, Collection $paketSoal): ?array
    {
        if (! $request->user() || ! $kuisLms->menggunakanTimer() || $paketSoal->isEmpty()) {
            return null;
        }

        $sessionKey = $this->sessionKeyTimerKuis($request, $kuisLms);
        $signaturePaket = $this->signaturePaketSoal($paketSoal);
        $timerTersimpan = $request->session()->get($sessionKey, []);
        $timer = is_array($timerTersimpan) ? $timerTersimpan : [];

        $butuhTimerBaru = ! filled($timer['berakhir_pada'] ?? null)
            || ! filled($timer['dimulai_pada'] ?? null)
            || (string) ($timer['paket_signature'] ?? '') !== $signaturePaket;

        if ($butuhTimerBaru) {
            $timer = [
                'dimulai_pada' => now()->toIso8601String(),
                'berakhir_pada' => now()->addMinutes((int) $kuisLms->durasi_menit)->toIso8601String(),
                'durasi_menit' => (int) $kuisLms->durasi_menit,
                'paket_signature' => $signaturePaket,
            ];

            $request->session()->put($sessionKey, $timer);
        }

        $dimulaiPada = Carbon::parse($timer['dimulai_pada']);
        $berakhirPada = Carbon::parse($timer['berakhir_pada']);
        $sisaDetik = max(0, now()->diffInSeconds($berakhirPada, false));

        return [
            'dimulai_pada' => $dimulaiPada,
            'berakhir_pada' => $berakhirPada,
            'durasi_menit' => (int) ($timer['durasi_menit'] ?? $kuisLms->durasi_menit),
            'sisa_detik' => $sisaDetik,
            'sudah_habis' => $sisaDetik === 0,
        ];
    }

    private function signaturePaketSoal(Collection $paketSoal): string
    {
        return md5((string) $paketSoal
            ->pluck('kode')
            ->join('|'));
    }

    private function lupakanPaketSoal(Request $request, KuisLms $kuisLms): void
    {
        if ($request->user()) {
            $request->session()->forget($this->sessionKeyPaketSoal($request, $kuisLms));
            $request->session()->forget($this->sessionKeyTimerKuis($request, $kuisLms));
        }
    }

    private function assertBankSoalKompatibel(KuisLms $kuisLms, BankSoalLms $bankSoalLms): void
    {
        if (! $bankSoalLms->aktif) {
            throw ValidationException::withMessages([
                'bank_soal_lms_id' => 'Hanya bank soal aktif yang bisa ditempelkan ke kuis.',
            ]);
        }

        if (filled($bankSoalLms->kursus_id) && (int) $bankSoalLms->kursus_id !== (int) $kuisLms->kursus_id) {
            throw ValidationException::withMessages([
                'bank_soal_lms_id' => 'Bank soal hanya bisa ditempelkan jika kursusnya sama atau global.',
            ]);
        }
    }

    private function assertBukanBankSoal(KuisLms $kuisLms): void
    {
        if ($kuisLms->menggunakanBankSoal()) {
            throw ValidationException::withMessages([
                'kuis' => 'Kuis ini menggunakan bank soal. Nonaktifkan mode bank soal jika ingin mengelola pertanyaan manual.',
            ]);
        }
    }

    private function assertGunakanBankSoal(KuisLms $kuisLms): void
    {
        if (! $kuisLms->menggunakanBankSoal()) {
            throw ValidationException::withMessages([
                'kuis' => 'Aktifkan mode bank soal terlebih dahulu untuk menempelkan soal reusable.',
            ]);
        }
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function authorizeQuizAccess(Request $request, KuisLms $kuisLms): void
    {
        if (! $kuisLms->aktif && ! $request->user()?->adalahSuperadmin()) {
            abort(404);
        }
    }

    private function filterQuery(Request $request): array
    {
        $query = [];

        if ($target = $this->selectedTargetTipe($request->input('filter_target_tipe'))) {
            $query['target_tipe'] = $target;
        }

        if ($kursus = $this->selectedKursus($request->input('filter_kursus'))) {
            $query['kursus'] = $kursus->id;
        }

        if ($materi = $this->selectedMateri($request->input('filter_materi'))) {
            $query['materi'] = $materi->id;
        }

        if ($kataKunci = $this->selectedKataKunci($request->input('filter_q'))) {
            $query['q'] = $kataKunci;
        }

        return $query;
    }
}
