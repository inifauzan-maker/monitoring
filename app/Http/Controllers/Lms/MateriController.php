<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\KuisLms;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Models\ProgresBelajarMateri;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderHalaman($request);
    }

    public function show(Request $request, MateriKursus $materiKursus): View
    {
        $pengguna = $request->user();

        $materiKursus->load([
            'kursus.materiKursus' => function ($query) use ($pengguna) {
                $query->with([
                    'progresBelajar' => fn ($relasi) => $relasi->where('user_id', $pengguna->id),
                ])->orderBy('urutan');
            },
            'progresBelajar' => fn ($relasi) => $relasi->where('user_id', $pengguna->id),
        ]);

        $progres = $materiKursus->progresBelajar->first()
            ?? new ProgresBelajarMateri([
                'user_id' => $pengguna->id,
                'materi_kursus_id' => $materiKursus->id,
                'detik_terakhir' => 0,
                'persen_progres' => 0,
            ]);

        $daftarMateri = $materiKursus->kursus?->materiKursus ?? collect();
        $posisiMateri = $daftarMateri->search(fn (MateriKursus $item) => $item->id === $materiKursus->id);
        $materiSebelumnya = $posisiMateri !== false ? $daftarMateri->get($posisiMateri - 1) : null;
        $materiBerikutnya = $posisiMateri !== false ? $daftarMateri->get($posisiMateri + 1) : null;
        $jumlahMateri = $daftarMateri->count();
        $jumlahSelesai = $daftarMateri->filter(fn (MateriKursus $item) => $item->progresBelajar->first()?->sudahSelesai())->count();
        $persenKursus = $jumlahMateri > 0 ? (int) round(($jumlahSelesai / $jumlahMateri) * 100) : 0;
        $kuisTersedia = KuisLms::query()
            ->withCount(['pertanyaan', 'bankSoal'])
            ->with([
                'hasilPengguna' => fn ($query) => $query->where('user_id', $pengguna->id),
            ])
            ->where('aktif', true)
            ->where(function ($query) use ($materiKursus) {
                $query->where(function ($builder) use ($materiKursus) {
                    $builder->where('target_tipe', 'materi')
                        ->where('materi_kursus_id', $materiKursus->id);
                })->orWhere(function ($builder) use ($materiKursus) {
                    $builder->where('target_tipe', 'kursus')
                        ->where('kursus_id', $materiKursus->kursus_id);
                });
            })
            ->latest()
            ->get();

        PencatatLogAktivitas::catat(
            $request,
            'materi',
            'lihat',
            'Halaman materi LMS dibuka.',
            $materiKursus,
            [
                'kursus_id' => $materiKursus->kursus_id,
                'kursus' => $materiKursus->kursus?->judul,
                'judul' => $materiKursus->judul,
                'youtube_id' => $materiKursus->youtube_id,
            ],
        );

        return view('lms.materi.show', [
            'materi' => $materiKursus,
            'progres' => $progres,
            'daftarMateri' => $daftarMateri,
            'materiSebelumnya' => $materiSebelumnya,
            'materiBerikutnya' => $materiBerikutnya,
            'persenKursus' => $persenKursus,
            'jumlahMateri' => $jumlahMateri,
            'jumlahSelesai' => $jumlahSelesai,
            'kuisTersedia' => $kuisTersedia,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->validatedData($request);
        $materi = MateriKursus::create($data);
        $materi->load('kursus');

        PencatatLogAktivitas::catat(
            $request,
            'materi',
            'tambah',
            'Materi kursus baru ditambahkan.',
            $materi,
            [
                'kursus_id' => $materi->kursus_id,
                'kursus' => $materi->kursus?->judul,
                'judul' => $materi->judul,
                'youtube_id' => $materi->youtube_id,
                'urutan' => $materi->urutan,
            ],
        );

        return redirect()
            ->route('lms.materi', $this->filterQuery($request->input('filter_kursus'), $request->input('filter_q')))
            ->with('status', 'Materi berhasil ditambahkan.');
    }

    public function edit(Request $request, MateriKursus $materiKursus): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderHalaman($request, $materiKursus);
    }

    public function update(Request $request, MateriKursus $materiKursus): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $snapshotSebelum = [
            'kursus_id' => $materiKursus->kursus_id,
            'judul' => $materiKursus->judul,
            'youtube_id' => $materiKursus->youtube_id,
            'durasi_detik' => $materiKursus->durasi_detik,
            'urutan' => $materiKursus->urutan,
        ];

        $data = $this->validatedData($request, $materiKursus);
        $materiKursus->update($data);
        $materiKursus->load('kursus');

        $snapshotSesudah = [
            'kursus_id' => $materiKursus->kursus_id,
            'judul' => $materiKursus->judul,
            'youtube_id' => $materiKursus->youtube_id,
            'durasi_detik' => $materiKursus->durasi_detik,
            'urutan' => $materiKursus->urutan,
        ];

        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();

        PencatatLogAktivitas::catat(
            $request,
            'materi',
            'ubah',
            'Materi kursus diperbarui.',
            $materiKursus,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
                'kursus' => $materiKursus->kursus?->judul,
            ],
        );

        return redirect()
            ->route('lms.materi', $this->filterQuery($request->input('filter_kursus'), $request->input('filter_q')))
            ->with('status', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, MateriKursus $materiKursus): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $materiKursus->load('kursus');

        PencatatLogAktivitas::catat(
            $request,
            'materi',
            'hapus',
            'Materi kursus dihapus.',
            $materiKursus,
            [
                'kursus_id' => $materiKursus->kursus_id,
                'kursus' => $materiKursus->kursus?->judul,
                'judul' => $materiKursus->judul,
                'youtube_id' => $materiKursus->youtube_id,
                'urutan' => $materiKursus->urutan,
            ],
        );

        $materiKursus->delete();

        return redirect()
            ->route('lms.materi', $this->filterQuery($request->input('filter_kursus'), $request->input('filter_q')))
            ->with('status', 'Materi berhasil dihapus.');
    }

    private function renderHalaman(Request $request, ?MateriKursus $editItem = null): View
    {
        $kursusDipilih = $this->selectedKursus($request->query('kursus'));
        $kataKunci = $this->selectedKataKunci($request->query('q'));
        $items = $this->queryItems($kursusDipilih?->id, $kataKunci)->get();
        $opsiKursus = Kursus::query()->orderBy('judul')->get(['id', 'judul']);

        return view('lms.materi.index', [
            'items' => $items,
            'opsiKursus' => $opsiKursus,
            'kursusDipilih' => $kursusDipilih,
            'kataKunci' => $kataKunci,
            'editItem' => $editItem?->load('kursus'),
        ]);
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function validatedData(Request $request, ?MateriKursus $materiKursus = null): array
    {
        $validated = $request->validate([
            'kursus_id' => ['required', 'integer', 'exists:kursus,id'],
            'judul' => ['required', 'string', 'max:180'],
            'youtube_id' => ['required', 'string', 'max:60'],
            'durasi_detik' => ['nullable', 'integer', 'min:0'],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ], [], [
            'kursus_id' => 'kursus',
            'judul' => 'judul materi',
            'youtube_id' => 'YouTube ID',
            'durasi_detik' => 'durasi detik',
            'urutan' => 'urutan',
        ]);

        $validated['judul'] = trim((string) $validated['judul']);
        $validated['youtube_id'] = trim((string) $validated['youtube_id']);
        $validated['durasi_detik'] = (int) ($validated['durasi_detik'] ?? 0);
        $validated['urutan'] = (int) ($validated['urutan'] ?? ($this->urutanDefault((int) $validated['kursus_id'], $materiKursus?->id)));

        return $validated;
    }

    private function urutanDefault(int $kursusId, ?int $ignoreId = null): int
    {
        return (int) MateriKursus::query()
            ->where('kursus_id', $kursusId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->max('urutan') + 1;
    }

    private function selectedKursus(mixed $nilai): ?Kursus
    {
        $nilai = (int) $nilai;

        return $nilai > 0 ? Kursus::query()->find($nilai) : null;
    }

    private function selectedKataKunci(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return $nilai !== '' ? $nilai : null;
    }

    private function filterQuery(?string $kursusId, ?string $kataKunci): array
    {
        $query = [];
        $selectedKursus = $this->selectedKursus($kursusId);

        if ($selectedKursus) {
            $query['kursus'] = $selectedKursus->id;
        }

        if ($kataKunci = $this->selectedKataKunci($kataKunci)) {
            $query['q'] = $kataKunci;
        }

        return $query;
    }

    private function queryItems(?int $kursusId, ?string $kataKunci)
    {
        return MateriKursus::query()
            ->with('kursus')
            ->when($kursusId, fn ($query) => $query->where('kursus_id', $kursusId))
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($builder) use ($kataKunci) {
                    $builder->where('judul', 'like', '%'.$kataKunci.'%')
                        ->orWhere('youtube_id', 'like', '%'.$kataKunci.'%')
                        ->orWhereHas('kursus', fn ($relasi) => $relasi->where('judul', 'like', '%'.$kataKunci.'%'));
                });
            })
            ->orderBy('kursus_id')
            ->orderBy('urutan')
            ->orderBy('judul');
    }
}
