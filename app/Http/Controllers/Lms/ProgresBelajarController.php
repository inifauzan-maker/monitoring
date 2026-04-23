<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\ProgresBelajarMateri;
use App\Models\User;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProgresBelajarController extends Controller
{
    private const OPSI_STATUS = [
        'belum_mulai' => 'Belum Mulai',
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
    ];

    public function index(Request $request): View
    {
        $penggunaTarget = $this->resolvePenggunaTarget($request);
        $kursusDipilih = $this->selectedKursus($request->query('kursus'));
        $statusDipilih = $this->selectedStatus($request->query('status'));
        $kataKunci = $this->selectedKataKunci($request->query('q'));
        $materiDipilih = $this->selectedMateri($request->query('materi'), $penggunaTarget->id);

        $items = $this->queryItems($penggunaTarget->id, $kursusDipilih?->id, $statusDipilih, $kataKunci)->get();
        $ringkasanKursus = $this->ringkasanKursus($penggunaTarget->id, $kursusDipilih?->id);
        $ringkasanUtama = $this->ringkasanUtama($items);

        return view('lms.progres_belajar.index', [
            'items' => $items,
            'ringkasanKursus' => $ringkasanKursus,
            'ringkasanUtama' => $ringkasanUtama,
            'opsiKursus' => Kursus::query()->orderBy('judul')->get(['id', 'judul']),
            'opsiMateri' => $this->opsiMateriForm($kursusDipilih?->id),
            'opsiPengguna' => $request->user()->adalahSuperadmin()
                ? User::query()->orderBy('name')->get(['id', 'name', 'email'])
                : collect(),
            'opsiStatus' => self::OPSI_STATUS,
            'penggunaTarget' => $penggunaTarget,
            'kursusDipilih' => $kursusDipilih,
            'statusDipilih' => $statusDipilih,
            'kataKunci' => $kataKunci,
            'materiDipilih' => $materiDipilih,
            'isSuperadmin' => $request->user()->adalahSuperadmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pengguna_id' => ['nullable', 'integer', 'exists:users,id'],
            'materi_kursus_id' => ['required', 'integer', 'exists:materi_kursus,id'],
            'detik_terakhir' => ['nullable', 'integer', 'min:0'],
            'persen_progres' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tandai_selesai' => ['nullable', 'boolean'],
            'filter_pengguna' => ['nullable', 'integer'],
            'filter_kursus' => ['nullable', 'integer'],
            'filter_status' => ['nullable', 'string'],
            'filter_q' => ['nullable', 'string'],
        ], [], [
            'pengguna_id' => 'pengguna',
            'materi_kursus_id' => 'materi',
            'detik_terakhir' => 'detik terakhir',
            'persen_progres' => 'persen progres',
            'tandai_selesai' => 'tandai selesai',
        ]);

        $aktor = $request->user();
        $penggunaTarget = $aktor->adalahSuperadmin() && filled($validated['pengguna_id'] ?? null)
            ? User::query()->findOrFail((int) $validated['pengguna_id'])
            : $aktor;

        $materi = MateriKursus::query()->with('kursus')->findOrFail((int) $validated['materi_kursus_id']);
        $progres = $this->simpanProgres($request, $penggunaTarget, $materi, $validated, true);

        return redirect()
            ->route('lms.progres_belajar', $this->filterQuery($request, $penggunaTarget->id, $materi->id))
            ->with('status', 'Progres belajar berhasil diperbarui.');
    }

    public function sinkronOtomatis(Request $request, MateriKursus $materiKursus): JsonResponse
    {
        $validated = $request->validate([
            'detik_terakhir' => ['nullable', 'integer', 'min:0'],
            'persen_progres' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tandai_selesai' => ['nullable', 'boolean'],
        ], [], [
            'detik_terakhir' => 'detik terakhir',
            'persen_progres' => 'persen progres',
            'tandai_selesai' => 'tandai selesai',
        ]);

        $materiKursus->loadMissing('kursus');
        $progres = $this->simpanProgres($request, $request->user(), $materiKursus, $validated, false);

        return response()->json([
            'status' => 'ok',
            'persen_progres' => $progres->persen_progres,
            'detik_terakhir' => $progres->detik_terakhir,
            'selesai_pada' => optional($progres->selesai_pada)?->toISOString(),
            'label_status' => $progres->labelStatus(),
        ]);
    }

    private function resolvePenggunaTarget(Request $request): User
    {
        $aktor = $request->user();

        if (! $aktor->adalahSuperadmin()) {
            return $aktor;
        }

        $idPengguna = (int) $request->query('pengguna');

        return $idPengguna > 0
            ? User::query()->findOrFail($idPengguna)
            : $aktor;
    }

    private function selectedKursus(mixed $nilai): ?Kursus
    {
        $nilai = (int) $nilai;

        return $nilai > 0 ? Kursus::query()->find($nilai) : null;
    }

    private function selectedMateri(mixed $nilai, int $penggunaId): ?MateriKursus
    {
        $nilai = (int) $nilai;

        if ($nilai <= 0) {
            return null;
        }

        return MateriKursus::query()
            ->with('kursus')
            ->with(['progresBelajar' => fn ($query) => $query->where('user_id', $penggunaId)])
            ->find($nilai);
    }

    private function selectedStatus(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return array_key_exists($nilai, self::OPSI_STATUS) ? $nilai : null;
    }

    private function selectedKataKunci(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return $nilai !== '' ? $nilai : null;
    }

    private function queryItems(int $penggunaId, ?int $kursusId, ?string $status, ?string $kataKunci)
    {
        return MateriKursus::query()
            ->with('kursus')
            ->with(['progresBelajar' => fn ($query) => $query->where('user_id', $penggunaId)])
            ->when($kursusId, fn ($query) => $query->where('kursus_id', $kursusId))
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($builder) use ($kataKunci) {
                    $builder->where('judul', 'like', '%'.$kataKunci.'%')
                        ->orWhere('youtube_id', 'like', '%'.$kataKunci.'%')
                        ->orWhereHas('kursus', fn ($relasi) => $relasi->where('judul', 'like', '%'.$kataKunci.'%'));
                });
            })
            ->when($status === 'belum_mulai', function ($query) use ($penggunaId) {
                $query->whereDoesntHave('progresBelajar', function ($relasi) use ($penggunaId) {
                    $relasi->where('user_id', $penggunaId)
                        ->where(function ($builder) {
                            $builder->where('persen_progres', '>', 0)
                                ->orWhereNotNull('selesai_pada');
                        });
                });
            })
            ->when($status === 'berjalan', function ($query) use ($penggunaId) {
                $query->whereHas('progresBelajar', function ($relasi) use ($penggunaId) {
                    $relasi->where('user_id', $penggunaId)
                        ->whereNull('selesai_pada')
                        ->where('persen_progres', '>', 0)
                        ->where('persen_progres', '<', 100);
                });
            })
            ->when($status === 'selesai', function ($query) use ($penggunaId) {
                $query->whereHas('progresBelajar', function ($relasi) use ($penggunaId) {
                    $relasi->where('user_id', $penggunaId)
                        ->where(function ($builder) {
                            $builder->whereNotNull('selesai_pada')
                                ->orWhere('persen_progres', '>=', 100);
                        });
                });
            })
            ->orderBy('kursus_id')
            ->orderBy('urutan')
            ->orderBy('judul');
    }

    private function ringkasanKursus(int $penggunaId, ?int $kursusId = null): Collection
    {
        return Kursus::query()
            ->with([
                'materiKursus' => function ($query) use ($penggunaId) {
                    $query->with(['progresBelajar' => fn ($relasi) => $relasi->where('user_id', $penggunaId)]);
                },
            ])
            ->when($kursusId, fn ($query) => $query->where('id', $kursusId))
            ->orderBy('judul')
            ->get()
            ->map(function (Kursus $kursus) {
                $totalMateri = $kursus->materiKursus->count();
                $selesai = 0;
                $berjalan = 0;
                $akumulasiPersen = 0;

                foreach ($kursus->materiKursus as $materi) {
                    $progres = $materi->progresBelajar->first();
                    $persen = (int) ($progres?->persen_progres ?? 0);
                    $akumulasiPersen += $persen;

                    if ($progres?->sudahSelesai()) {
                        $selesai++;
                    } elseif ($progres?->sedangBerjalan()) {
                        $berjalan++;
                    }
                }

                $kursus->total_materi_lms = $totalMateri;
                $kursus->materi_selesai_lms = $selesai;
                $kursus->materi_berjalan_lms = $berjalan;
                $kursus->persen_selesai_lms = $totalMateri > 0
                    ? (int) round(($selesai / $totalMateri) * 100)
                    : 0;
                $kursus->rata_progres_lms = $totalMateri > 0
                    ? (int) round($akumulasiPersen / $totalMateri)
                    : 0;

                return $kursus;
            })
            ->filter(fn (Kursus $kursus) => $kursus->total_materi_lms > 0)
            ->values();
    }

    private function ringkasanUtama(Collection $items): array
    {
        $jumlahMateri = $items->count();
        $jumlahKursus = $items->pluck('kursus_id')->unique()->count();
        $jumlahSelesai = 0;
        $jumlahBerjalan = 0;
        $akumulasiPersen = 0;

        foreach ($items as $item) {
            $progres = $item->progresBelajar->first();
            $persen = (int) ($progres?->persen_progres ?? 0);
            $akumulasiPersen += $persen;

            if ($progres?->sudahSelesai()) {
                $jumlahSelesai++;
            } elseif ($progres?->sedangBerjalan()) {
                $jumlahBerjalan++;
            }
        }

        return [
            'jumlah_kursus' => $jumlahKursus,
            'jumlah_materi' => $jumlahMateri,
            'jumlah_selesai' => $jumlahSelesai,
            'jumlah_berjalan' => $jumlahBerjalan,
            'rata_progres' => $jumlahMateri > 0 ? (int) round($akumulasiPersen / $jumlahMateri) : 0,
        ];
    }

    private function opsiMateriForm(?int $kursusId = null): Collection
    {
        return MateriKursus::query()
            ->with('kursus')
            ->when($kursusId, fn ($query) => $query->where('kursus_id', $kursusId))
            ->orderBy('kursus_id')
            ->orderBy('urutan')
            ->orderBy('judul')
            ->get();
    }

    private function resolvePersenProgres(MateriKursus $materi, array $validated, int $detikTerakhir, int $persenSaatIni): int
    {
        if (array_key_exists('persen_progres', $validated) && $validated['persen_progres'] !== null) {
            return (int) $validated['persen_progres'];
        }

        if ($detikTerakhir > 0 && (int) $materi->durasi_detik > 0) {
            return (int) min(100, round(($detikTerakhir / (int) $materi->durasi_detik) * 100));
        }

        return $persenSaatIni;
    }

    private function simpanProgres(
        Request $request,
        User $penggunaTarget,
        MateriKursus $materi,
        array $validated,
        bool $catatLog = true,
    ): ProgresBelajarMateri {
        $progres = ProgresBelajarMateri::query()->firstOrNew([
            'user_id' => $penggunaTarget->id,
            'materi_kursus_id' => $materi->id,
        ]);

        $snapshotSebelum = [
            'user_id' => $progres->user_id,
            'materi_kursus_id' => $progres->materi_kursus_id,
            'detik_terakhir' => (int) ($progres->detik_terakhir ?? 0),
            'persen_progres' => (int) ($progres->persen_progres ?? 0),
            'selesai_pada' => optional($progres->selesai_pada)?->toDateTimeString(),
        ];

        $detikTerakhir = array_key_exists('detik_terakhir', $validated)
            ? (int) ($validated['detik_terakhir'] ?? 0)
            : (int) ($progres->detik_terakhir ?? 0);

        $persenProgres = $this->resolvePersenProgres($materi, $validated, $detikTerakhir, (int) ($progres->persen_progres ?? 0));
        $tandaiSelesai = (bool) ($validated['tandai_selesai'] ?? false);

        if ($tandaiSelesai || $persenProgres >= 100) {
            $persenProgres = 100;
            $progres->selesai_pada = $progres->selesai_pada ?? now();

            if ((int) $materi->durasi_detik > 0) {
                $detikTerakhir = max($detikTerakhir, (int) $materi->durasi_detik);
            }
        } elseif (array_key_exists('persen_progres', $validated) || array_key_exists('detik_terakhir', $validated) || $request->has('tandai_selesai')) {
            $progres->selesai_pada = null;
        }

        $progres->user_id = $penggunaTarget->id;
        $progres->materi_kursus_id = $materi->id;
        $progres->detik_terakhir = max(0, $detikTerakhir);
        $progres->persen_progres = max(0, min(100, $persenProgres));
        $progres->save();

        if ($catatLog) {
            $snapshotSesudah = [
                'user_id' => $progres->user_id,
                'materi_kursus_id' => $progres->materi_kursus_id,
                'detik_terakhir' => (int) $progres->detik_terakhir,
                'persen_progres' => (int) $progres->persen_progres,
                'selesai_pada' => optional($progres->selesai_pada)?->toDateTimeString(),
            ];

            PencatatLogAktivitas::catat(
                $request,
                'progres_belajar',
                'ubah',
                'Progres belajar materi diperbarui.',
                $progres,
                [
                    'pengguna_target_id' => $penggunaTarget->id,
                    'pengguna_target' => $penggunaTarget->name,
                    'email_pengguna_target' => $penggunaTarget->email,
                    'kursus_id' => $materi->kursus_id,
                    'kursus' => $materi->kursus?->judul,
                    'materi' => $materi->judul,
                    'youtube_id' => $materi->youtube_id,
                    'sebelum' => $snapshotSebelum,
                    'sesudah' => $snapshotSesudah,
                ],
            );
        }

        return $progres;
    }

    private function filterQuery(Request $request, int $penggunaId, ?int $materiId = null): array
    {
        $query = [];

        if ($request->user()->adalahSuperadmin()) {
            $filterPengguna = (int) ($request->input('filter_pengguna') ?: $penggunaId);

            if ($filterPengguna > 0) {
                $query['pengguna'] = $filterPengguna;
            }
        }

        $filterKursus = (int) $request->input('filter_kursus');

        if ($filterKursus > 0) {
            $query['kursus'] = $filterKursus;
        }

        $filterStatus = $this->selectedStatus($request->input('filter_status'));

        if ($filterStatus) {
            $query['status'] = $filterStatus;
        }

        $filterQ = $this->selectedKataKunci($request->input('filter_q'));

        if ($filterQ) {
            $query['q'] = $filterQ;
        }

        if ($materiId) {
            $query['materi'] = $materiId;
        }

        return $query;
    }
}
