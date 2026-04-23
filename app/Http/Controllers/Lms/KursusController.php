<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KursusController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderHalaman($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->validatedData($request);
        $kursus = Kursus::create($data);

        PencatatLogAktivitas::catat(
            $request,
            'kursus',
            'tambah',
            'Kursus LMS baru ditambahkan.',
            $kursus,
            [
                'judul' => $kursus->judul,
                'slug' => $kursus->slug,
            ],
        );

        return redirect()
            ->route('lms.kursus', $this->filterQuery($request->input('filter_q')))
            ->with('status', 'Kursus berhasil ditambahkan.');
    }

    public function edit(Request $request, Kursus $kursus): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderHalaman($request, $kursus);
    }

    public function update(Request $request, Kursus $kursus): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $snapshotSebelum = [
            'judul' => $kursus->judul,
            'slug' => $kursus->slug,
            'ringkasan' => $kursus->ringkasan,
            'thumbnail_url' => $kursus->thumbnail_url,
        ];

        $data = $this->validatedData($request, $kursus);
        $kursus->update($data);

        $snapshotSesudah = [
            'judul' => $kursus->judul,
            'slug' => $kursus->slug,
            'ringkasan' => $kursus->ringkasan,
            'thumbnail_url' => $kursus->thumbnail_url,
        ];

        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();

        PencatatLogAktivitas::catat(
            $request,
            'kursus',
            'ubah',
            'Kursus LMS diperbarui.',
            $kursus,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );

        return redirect()
            ->route('lms.kursus', $this->filterQuery($request->input('filter_q')))
            ->with('status', 'Kursus berhasil diperbarui.');
    }

    public function destroy(Request $request, Kursus $kursus): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        PencatatLogAktivitas::catat(
            $request,
            'kursus',
            'hapus',
            'Kursus LMS dihapus.',
            $kursus,
            [
                'judul' => $kursus->judul,
                'slug' => $kursus->slug,
            ],
        );

        $kursus->delete();

        return redirect()
            ->route('lms.kursus', $this->filterQuery($request->input('filter_q')))
            ->with('status', 'Kursus berhasil dihapus.');
    }

    private function renderHalaman(Request $request, ?Kursus $editItem = null): View
    {
        $kataKunci = $this->selectedKataKunci($request->query('q'));
        $items = $this->queryItems($kataKunci)->get();

        return view('lms.kursus.index', [
            'items' => $items,
            'kataKunci' => $kataKunci,
            'editItem' => $editItem,
        ]);
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function validatedData(Request $request, ?Kursus $kursus = null): array
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180'],
            'ringkasan' => ['nullable', 'string', 'max:1000'],
            'thumbnail_url' => ['nullable', 'url'],
        ], [], [
            'judul' => 'judul kursus',
            'slug' => 'slug',
            'ringkasan' => 'ringkasan',
            'thumbnail_url' => 'URL thumbnail',
        ]);

        $slug = Str::slug((string) (($validated['slug'] ?? null) ?: $validated['judul']));
        $validated['slug'] = $this->uniqueSlug($slug, $kursus?->id);
        $validated['ringkasan'] = filled($validated['ringkasan'] ?? null)
            ? trim((string) $validated['ringkasan'])
            : null;
        $validated['thumbnail_url'] = filled($validated['thumbnail_url'] ?? null)
            ? trim((string) $validated['thumbnail_url'])
            : null;

        return $validated;
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $baseSlug = $slug !== '' ? $slug : 'kursus';
        $candidate = $baseSlug;
        $counter = 1;

        while (
            Kursus::query()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function selectedKataKunci(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return $nilai !== '' ? $nilai : null;
    }

    private function filterQuery(?string $kataKunci): array
    {
        $query = [];

        if ($kataKunci = $this->selectedKataKunci($kataKunci)) {
            $query['q'] = $kataKunci;
        }

        return $query;
    }

    private function queryItems(?string $kataKunci)
    {
        return Kursus::query()
            ->withCount(['materiKursus', 'kuisLms'])
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($builder) use ($kataKunci) {
                    $builder->where('judul', 'like', '%'.$kataKunci.'%')
                        ->orWhere('slug', 'like', '%'.$kataKunci.'%')
                        ->orWhere('ringkasan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->latest();
    }
}
