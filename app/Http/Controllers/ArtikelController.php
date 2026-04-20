<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Artikel;
use App\Models\ArtikelRevisi;
use App\Models\KategoriArtikel;
use App\Models\PresetEditorialPengguna;
use App\Models\User;
use App\Support\PencatatLogAktivitas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArtikelController extends Controller
{
    public function index(Request $request): View
    {
        $kataKunci = trim((string) $request->query('q'));
        $kategoriDipilih = $this->selectedKategori($request->query('kategori'));

        $query = Artikel::query()
            ->with(['kategori', 'penulis'])
            ->diterbitkan()
            ->when($kataKunci !== '', function (Builder $builder) use ($kataKunci) {
                $builder->where(function (Builder $query) use ($kataKunci) {
                    $query->where('judul', 'like', '%'.$kataKunci.'%')
                        ->orWhere('ringkasan', 'like', '%'.$kataKunci.'%')
                        ->orWhere('kata_kunci_utama', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($kategoriDipilih, fn (Builder $builder) => $builder->where('kategori_artikel_id', $kategoriDipilih->id));

        $items = (clone $query)
            ->orderByDesc('diterbitkan_pada')
            ->paginate(12)
            ->withQueryString();

        $ringkasan = (clone $query)->get();

        $kategori = KategoriArtikel::query()
            ->withCount([
                'artikel as jumlah_artikel_diterbitkan' => fn (Builder $builder) => $builder->diterbitkan(),
            ])
            ->orderBy('nama')
            ->get();

        return view('tools.artikel.index', [
            'items' => $items,
            'kategori' => $kategori,
            'kataKunci' => $kataKunci,
            'kategoriDipilih' => $kategoriDipilih,
            'jumlahArtikel' => $ringkasan->count(),
            'jumlahKategoriAktif' => $ringkasan->pluck('kategori_artikel_id')->filter()->unique()->count(),
            'tanggalTerbitTerbaru' => $ringkasan->max('diterbitkan_pada'),
            'jumlahSiapTerbit' => $request->user()?->artikel()->get()->filter(fn (Artikel $artikel) => $artikel->evaluasiKesiapan()['siap_terbit'])->count() ?? 0,
            'artikelSayaTerbaru' => $request->user()
                ?->artikel()
                ->with('kategori')
                ->latest('updated_at')
                ->limit(5)
                ->get() ?? collect(),
        ]);
    }

    public function saya(Request $request): View
    {
        $statusDipilih = $this->selectedStatus($request->query('status'));

        $query = $request->user()
            ->artikel()
            ->with(['kategori'])
            ->withCount('revisi')
            ->when($statusDipilih === 'draft', fn (Builder $builder) => $builder->where('sudah_diterbitkan', false))
            ->when($statusDipilih === 'terjadwal', fn (Builder $builder) => $builder->terjadwal())
            ->when($statusDipilih === 'diterbitkan', fn (Builder $builder) => $builder->terbitAktif());

        $items = (clone $query)
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $ringkasan = $request->user()->artikel()->get();

        return view('tools.artikel.saya', [
            'items' => $items,
            'statusDipilih' => $statusDipilih,
            'jumlahSemua' => $ringkasan->count(),
            'jumlahDraft' => $ringkasan->filter(fn (Artikel $artikel) => $artikel->adalahDraft())->count(),
            'jumlahTerjadwal' => $ringkasan->filter(fn (Artikel $artikel) => $artikel->sedangTerjadwal())->count(),
            'jumlahTerbit' => $ringkasan->filter(fn (Artikel $artikel) => $artikel->sudahTerbitAktif())->count(),
            'jumlahSiapTerbit' => $ringkasan->filter(fn (Artikel $artikel) => $artikel->adalahDraft() && $artikel->evaluasiKesiapan()['siap_terbit'])->count(),
        ]);
    }

    public function dashboardEditorial(Request $request): View
    {
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'lihat',
            'Membuka dashboard editorial artikel.',
            'dashboard_editorial_artikel',
            [
                'filter' => $this->queryEditorialAktifDariRequest($request),
            ]
        );

        return view('tools.artikel.editorial', $this->dataDashboardEditorial($request));
    }

    public function simpanPresetEditorial(Request $request): RedirectResponse
    {
        $pengguna = $request->user();

        $validated = $request->validate([
            'nama_preset' => [
                'required',
                'string',
                'max:100',
                Rule::unique('preset_editorial_pengguna', 'nama_preset')->where('user_id', $pengguna->id),
            ],
            'penulis' => ['nullable', 'integer', 'exists:users,id'],
            'kategori' => ['nullable', 'integer', 'exists:kategori_artikel,id'],
            'status_editorial' => ['nullable', Rule::in(array_keys($this->opsiStatusEditorial()))],
            'basis_tanggal' => ['nullable', Rule::in(array_keys($this->opsiBasisTanggalEditorial()))],
            'tanggal_dari' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_sampai' => ['nullable', 'date_format:Y-m-d'],
        ], [], [
            'nama_preset' => 'nama preset',
            'penulis' => 'penulis',
            'kategori' => 'kategori',
            'status_editorial' => 'status editorial',
            'basis_tanggal' => 'basis tanggal',
            'tanggal_dari' => 'tanggal dari',
            'tanggal_sampai' => 'tanggal sampai',
        ]);

        $konfigurasiFilter = $this->konfigurasiFilterEditorialDariInput($validated);

        if ($konfigurasiFilter === []) {
            return redirect()
                ->route('tools.artikel.editorial')
                ->withErrors(['nama_preset' => 'Aktifkan minimal satu filter sebelum menyimpan preset kustom.']);
        }

        $preset = $pengguna->presetEditorialPengguna()->create([
            'nama_preset' => trim((string) $validated['nama_preset']),
            'konfigurasi_filter' => $konfigurasiFilter,
        ]);

        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'tambah',
            'Menyimpan preset editorial kustom.',
            $preset,
            [
                'nama_preset' => $preset->nama_preset,
                'konfigurasi_filter' => $preset->konfigurasi_filter,
            ]
        );

        return redirect()
            ->route('tools.artikel.editorial', array_merge($konfigurasiFilter, ['preset_pengguna' => $preset->id]))
            ->with('status', 'Preset kustom berhasil disimpan.');
    }

    public function hapusPresetEditorial(Request $request, PresetEditorialPengguna $presetEditorialPengguna): RedirectResponse
    {
        abort_unless((int) $presetEditorialPengguna->user_id === (int) $request->user()->id, 403, 'Akses ditolak.');

        $query = $this->queryEditorialAktifDariRequest($request);
        $ringkasanPreset = [
            'nama_preset' => $presetEditorialPengguna->nama_preset,
            'konfigurasi_filter' => $presetEditorialPengguna->konfigurasi_filter,
        ];

        if (($query['preset_pengguna'] ?? null) === (string) $presetEditorialPengguna->id) {
            unset($query['preset_pengguna']);
        }

        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'hapus',
            'Menghapus preset editorial kustom.',
            $presetEditorialPengguna,
            $ringkasanPreset
        );

        $presetEditorialPengguna->delete();

        return redirect()
            ->route('tools.artikel.editorial', $query)
            ->with('status', 'Preset kustom berhasil dihapus.');
    }

    public function create(): View
    {
        return view('tools.artikel.buat', [
            'kategori' => KategoriArtikel::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $artikel = $request->user()->artikel()->create(
            $this->validatedArtikelData($request)
        );

        $this->simpanRevisi($artikel, 'awal');
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'tambah',
            'Membuat draft artikel baru.',
            $artikel,
            [
                'artikel' => $this->ringkasanAuditArtikel($artikel),
            ]
        );

        return redirect()
            ->route('tools.artikel.edit', $artikel)
            ->with('status', 'Draft artikel berhasil dibuat.');
    }

    public function show(Artikel $artikel): View
    {
        abort_unless($artikel->sudahTerbitAktif(), 404);

        $artikel->load(['kategori', 'penulis']);

        return view('tools.artikel.show', [
            'artikel' => $artikel,
            'adalahPreview' => false,
        ]);
    }

    public function exportPdf(Request $request, Artikel $artikel): Response
    {
        if (! $artikel->sudahTerbitAktif()) {
            $this->otorisasiArtikel($request, $artikel);
        }

        $artikel->load(['kategori', 'penulis']);

        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'ekspor',
            'Mengekspor artikel ke PDF.',
            $artikel,
            [
                'mode' => $artikel->sudahTerbitAktif() ? 'publikasi' : 'preview',
                'artikel' => $this->ringkasanAuditArtikel($artikel),
            ]
        );

        return Pdf::loadView('tools.artikel.pdf.artikel', [
            'artikel' => $artikel,
            'adalahPreview' => ! $artikel->sudahTerbitAktif(),
        ])->download(Str::slug($artikel->judul ?: 'artikel').'.pdf');
    }

    public function exportDashboardPdf(Request $request): Response
    {
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'ekspor',
            'Mengekspor dashboard editorial artikel ke PDF.',
            'dashboard_editorial_artikel',
            [
                'filter' => $this->queryEditorialAktifDariRequest($request),
            ]
        );

        return Pdf::loadView('tools.artikel.pdf.editorial', $this->dataDashboardEditorial($request))
            ->setPaper('a4', 'portrait')
            ->download('dashboard-editorial-artikel.pdf');
    }

    public function preview(Request $request, Artikel $artikel): View
    {
        $this->otorisasiArtikel($request, $artikel);

        $artikel->load(['kategori', 'penulis']);

        return view('tools.artikel.show', [
            'artikel' => $artikel,
            'adalahPreview' => true,
        ]);
    }

    public function edit(Request $request, Artikel $artikel): View
    {
        $this->otorisasiArtikel($request, $artikel);

        $artikel->load([
            'kategori',
            'revisi' => fn ($builder) => $builder->with('penulis')->latest()->limit(8),
        ]);

        return view('tools.artikel.ubah', [
            'artikel' => $artikel,
            'kategori' => KategoriArtikel::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Artikel $artikel): RedirectResponse
    {
        $this->otorisasiArtikel($request, $artikel);
        $sebelum = $this->ringkasanAuditArtikel($artikel);
        $data = $this->validatedArtikelData($request, $artikel);

        $artikel->fill($data);
        $kolomDiubah = array_keys($artikel->getDirty());
        $artikel->save();

        $artikel = $artikel->fresh();
        $this->simpanRevisi($artikel, 'manual');
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'ubah',
            'Memperbarui draft artikel.',
            $artikel,
            [
                'sebelum' => $sebelum,
                'sesudah' => $this->ringkasanAuditArtikel($artikel),
                'kolom_diubah' => $kolomDiubah,
            ]
        );

        return redirect()
            ->route('tools.artikel.edit', $artikel)
            ->with('status', 'Draft artikel berhasil diperbarui.');
    }

    public function autosimpan(Request $request, Artikel $artikel): JsonResponse
    {
        $this->otorisasiArtikel($request, $artikel);

        $artikel->update(
            $this->validatedArtikelData($request, $artikel, true)
        );

        $this->simpanRevisi($artikel->fresh(), 'autosimpan');

        return response()->json([
            'message' => 'Draft tersimpan otomatis.',
            'saved_at' => $artikel->fresh()->updated_at?->timezone(config('app.timezone'))->format('H:i:s'),
        ]);
    }

    public function pulihkanRevisi(Request $request, Artikel $artikel, ArtikelRevisi $revisi): RedirectResponse
    {
        $this->otorisasiArtikel($request, $artikel);
        abort_unless($revisi->artikel_id === $artikel->id, 404);
        $sebelum = $this->ringkasanAuditArtikel($artikel);

        $this->simpanRevisi($artikel, 'sebelum_pulih', true);

        $artikel->update($this->payloadDariRevisi($revisi));

        $artikel = $artikel->fresh();
        $this->simpanRevisi($artikel, 'dipulihkan', true);
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'ubah',
            'Memulihkan revisi artikel.',
            $artikel,
            [
                'revisi_id' => $revisi->id,
                'tipe_pemicu_revisi' => $revisi->tipe_pemicu,
                'sebelum' => $sebelum,
                'sesudah' => $this->ringkasanAuditArtikel($artikel),
            ]
        );

        return redirect()
            ->route('tools.artikel.edit', $artikel)
            ->with('status', 'Revisi draft berhasil dipulihkan.');
    }

    public function terbitkan(Request $request, Artikel $artikel): RedirectResponse
    {
        $this->otorisasiArtikel($request, $artikel);
        $sebelum = $this->ringkasanAuditArtikel($artikel);

        if ($masalah = $this->masalahPublikasi($artikel)) {
            return redirect()
                ->route('tools.artikel.edit', $artikel)
                ->withErrors([
                    'publikasi' => 'Artikel belum bisa diterbitkan: '.implode(' ', $masalah),
                ]);
        }

        $artikel->update([
            'sudah_diterbitkan' => true,
            'diterbitkan_pada' => $artikel->diterbitkan_pada ?? now(),
        ]);

        $artikel->refresh();
        $tipePemicu = $artikel->sedangTerjadwal() ? 'dijadwalkan' : 'diterbitkan';
        $pesan = $artikel->sedangTerjadwal()
            ? 'Artikel berhasil dijadwalkan terbit.'
            : 'Artikel berhasil diterbitkan.';

        $this->simpanRevisi($artikel, $tipePemicu, true);
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'ubah',
            $pesan,
            $artikel,
            [
                'sebelum' => $sebelum,
                'sesudah' => $this->ringkasanAuditArtikel($artikel),
                'mode_publikasi' => $artikel->sedangTerjadwal() ? 'terjadwal' : 'diterbitkan',
            ]
        );

        return redirect()
            ->route('tools.artikel.edit', $artikel)
            ->with('status', $pesan);
    }

    public function batalkanTerbit(Request $request, Artikel $artikel): RedirectResponse
    {
        $this->otorisasiArtikel($request, $artikel);
        $sebelum = $this->ringkasanAuditArtikel($artikel);

        $artikel->update([
            'sudah_diterbitkan' => false,
            'diterbitkan_pada' => null,
        ]);

        $artikel = $artikel->fresh();
        $this->simpanRevisi($artikel, 'dibatalkan_terbit', true);
        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'ubah',
            'Mengembalikan artikel ke status draft.',
            $artikel,
            [
                'sebelum' => $sebelum,
                'sesudah' => $this->ringkasanAuditArtikel($artikel),
            ]
        );

        return redirect()
            ->route('tools.artikel.edit', $artikel)
            ->with('status', 'Artikel dikembalikan ke draft.');
    }

    public function destroy(Request $request, Artikel $artikel): RedirectResponse
    {
        $this->otorisasiArtikel($request, $artikel);
        $ringkasanArtikel = $this->ringkasanAuditArtikel($artikel);

        if ($artikel->gambar_unggulan_path) {
            Storage::disk('public')->delete($artikel->gambar_unggulan_path);
        }

        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'hapus',
            'Menghapus artikel.',
            $artikel,
            [
                'artikel' => $ringkasanArtikel,
            ]
        );

        $artikel->delete();

        return redirect()
            ->route('tools.artikel.saya')
            ->with('status', 'Artikel berhasil dihapus.');
    }

    private function selectedKategori(mixed $nilai): ?KategoriArtikel
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        return KategoriArtikel::query()
            ->where('slug', $nilai)
            ->orWhere('id', $nilai)
            ->first();
    }

    private function selectedPenulis(mixed $nilai): ?User
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        return User::query()
            ->where('id', $nilai)
            ->first();
    }

    private function selectedPresetEditorialPengguna(?User $pengguna, mixed $nilai): ?PresetEditorialPengguna
    {
        if (! $pengguna) {
            return null;
        }

        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        return $pengguna->presetEditorialPengguna()
            ->where('id', $nilai)
            ->first();
    }

    private function selectedStatus(mixed $nilai): ?string
    {
        return in_array($nilai, ['draft', 'terjadwal', 'diterbitkan'], true) ? $nilai : null;
    }

    private function selectedStatusEditorial(mixed $nilai): ?string
    {
        return in_array($nilai, array_keys($this->opsiStatusEditorial()), true) ? (string) $nilai : null;
    }

    private function selectedPresetEditorial(mixed $nilai): ?string
    {
        return in_array($nilai, array_keys($this->opsiPresetEditorial()), true) ? (string) $nilai : null;
    }

    private function selectedBasisTanggalEditorial(mixed $nilai): string
    {
        return in_array($nilai, array_keys($this->opsiBasisTanggalEditorial()), true)
            ? (string) $nilai
            : 'diperbarui';
    }

    private function selectedTanggalFilter(mixed $nilai): ?Carbon
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $nilai)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function otorisasiArtikel(Request $request, Artikel $artikel): void
    {
        abort_unless((int) $artikel->penulis_id === (int) $request->user()->id, 403, 'Akses ditolak.');
    }

    private function validatedArtikelData(Request $request, ?Artikel $artikel = null, bool $autosimpan = false): array
    {
        $sumberReferensi = collect($request->input('sumber_referensi', []))
            ->filter(fn ($sumber) => is_string($sumber) && trim($sumber) !== '')
            ->map(fn ($sumber) => trim($sumber))
            ->values()
            ->all();
        $checklistKesiapan = Artikel::normalisasiChecklistKesiapan($request->input('checklist_kesiapan', []));

        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('judul'))),
            'sumber_referensi' => $sumberReferensi,
            'checklist_kesiapan' => $checklistKesiapan,
        ]);

        $aturan = [
            'judul' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('artikel', 'slug')->ignore($artikel)],
            'kata_kunci_utama' => ['nullable', 'string', 'max:255'],
            'ringkasan' => ['required', 'string'],
            'konten' => ['required', 'string'],
            'kategori_artikel_id' => ['required', 'exists:kategori_artikel,id'],
            'tingkat_keahlian' => ['required', Rule::in(array_keys(Artikel::TINGKAT_KEAHLIAN))],
            'bio_penulis' => ['nullable', 'string'],
            'sumber_referensi' => ['nullable', 'array'],
            'sumber_referensi.*' => ['url'],
            'judul_seo' => ['nullable', 'string', 'max:60'],
            'deskripsi_seo' => ['nullable', 'string', 'max:160'],
            'outline_seo' => ['nullable', 'string'],
            'checklist_kesiapan' => ['nullable', 'array'],
            'diterbitkan_pada' => ['nullable', 'date'],
            'alt_gambar_unggulan' => ['nullable', 'string', 'max:255'],
        ];

        if (! $autosimpan) {
            $aturan['gambar_unggulan'] = ['nullable', 'image', 'max:3072'];
            $aturan['hapus_gambar_unggulan'] = ['sometimes', 'boolean'];
        }

        $validated = $request->validate($aturan, [], [
            'judul' => 'judul',
            'slug' => 'slug',
            'kata_kunci_utama' => 'kata kunci utama',
            'ringkasan' => 'ringkasan',
            'konten' => 'konten',
            'kategori_artikel_id' => 'kategori',
            'tingkat_keahlian' => 'tingkat keahlian',
            'bio_penulis' => 'bio penulis',
            'sumber_referensi' => 'sumber referensi',
            'judul_seo' => 'judul SEO',
            'deskripsi_seo' => 'deskripsi SEO',
            'outline_seo' => 'outline SEO',
            'checklist_kesiapan' => 'checklist kesiapan',
            'diterbitkan_pada' => 'jadwal terbit',
            'gambar_unggulan' => 'gambar unggulan',
            'alt_gambar_unggulan' => 'alt gambar unggulan',
        ]);

        foreach (['kata_kunci_utama', 'bio_penulis', 'judul_seo', 'deskripsi_seo', 'outline_seo', 'alt_gambar_unggulan', 'diterbitkan_pada'] as $field) {
            if (($validated[$field] ?? null) === '') {
                $validated[$field] = null;
            }
        }

        $validated['sumber_referensi'] = $validated['sumber_referensi'] ?: null;
        $validated['checklist_kesiapan'] = $checklistKesiapan;

        if (! $autosimpan) {
            if ($request->hasFile('gambar_unggulan')) {
                if ($artikel?->gambar_unggulan_path) {
                    Storage::disk('public')->delete($artikel->gambar_unggulan_path);
                }

                $validated['gambar_unggulan_path'] = $request->file('gambar_unggulan')->store('artikel-gambar-unggulan', 'public');
            } elseif ($request->boolean('hapus_gambar_unggulan') && $artikel?->gambar_unggulan_path) {
                Storage::disk('public')->delete($artikel->gambar_unggulan_path);
                $validated['gambar_unggulan_path'] = null;
                $validated['alt_gambar_unggulan'] = null;
            } elseif (! $artikel) {
                $validated['gambar_unggulan_path'] = null;
            }

            unset($validated['gambar_unggulan'], $validated['hapus_gambar_unggulan']);
        }

        return $validated;
    }

    private function masalahPublikasi(Artikel $artikel): array
    {
        return collect($artikel->evaluasiKesiapan()['checks'])
            ->filter(fn (array $check) => $check['blokir_publikasi'] && ! $check['ok'])
            ->pluck('pesan')
            ->all();
    }

    private function simpanRevisi(Artikel $artikel, string $tipePemicu, bool $paksaBuat = false): void
    {
        $snapshot = $artikel->snapshotRevisi();
        $revisiTerbaru = $artikel->revisi()->first();

        if (! $paksaBuat && $revisiTerbaru && $revisiTerbaru->snapshot === $snapshot) {
            return;
        }

        if (! $paksaBuat
            && $tipePemicu === 'autosimpan'
            && $revisiTerbaru
            && $revisiTerbaru->tipe_pemicu === 'autosimpan'
            && $revisiTerbaru->created_at?->gt(now()->subSeconds(90))) {
            $revisiTerbaru->update([
                'snapshot' => $snapshot,
            ]);

            return;
        }

        $artikel->revisi()->create([
            'penulis_id' => $artikel->penulis_id,
            'tipe_pemicu' => $tipePemicu,
            'snapshot' => $snapshot,
        ]);

        $idYangDipertahankan = $artikel->revisi()
            ->latest()
            ->limit(20)
            ->pluck('id');

        $artikel->revisi()
            ->whereNotIn('id', $idYangDipertahankan)
            ->delete();
    }

    private function payloadDariRevisi(ArtikelRevisi $revisi): array
    {
        $snapshot = $revisi->snapshot ?? [];

        return [
            'judul' => $snapshot['judul'] ?? '',
            'slug' => $snapshot['slug'] ?? Str::slug((string) ($snapshot['judul'] ?? 'artikel')),
            'kata_kunci_utama' => $snapshot['kata_kunci_utama'] ?? null,
            'ringkasan' => $snapshot['ringkasan'] ?? '',
            'konten' => $snapshot['konten'] ?? '',
            'kategori_artikel_id' => $snapshot['kategori_artikel_id'] ?? null,
            'tingkat_keahlian' => $snapshot['tingkat_keahlian'] ?? 'menengah',
            'bio_penulis' => $snapshot['bio_penulis'] ?? null,
            'sumber_referensi' => $snapshot['sumber_referensi'] ?? null,
            'judul_seo' => $snapshot['judul_seo'] ?? null,
            'deskripsi_seo' => $snapshot['deskripsi_seo'] ?? null,
            'outline_seo' => $snapshot['outline_seo'] ?? null,
            'checklist_kesiapan' => Artikel::normalisasiChecklistKesiapan($snapshot['checklist_kesiapan'] ?? []),
            'alt_gambar_unggulan' => $snapshot['alt_gambar_unggulan'] ?? null,
        ];
    }

    private function dataDashboardEditorial(Request $request): array
    {
        $presetEditorialDipilih = $this->selectedPresetEditorial($request->query('preset_editorial'));
        $presetPenggunaDipilih = $this->selectedPresetEditorialPengguna($request->user(), $request->query('preset_pengguna'));
        $kategoriDipilih = $this->selectedKategori($request->query('kategori'));
        $penulisDipilih = $this->selectedPenulis($request->query('penulis'));
        $statusEditorialDipilih = $this->selectedStatusEditorial($request->query('status_editorial'));
        $basisTanggalDipilih = $this->selectedBasisTanggalEditorial($request->query('basis_tanggal'));
        $tanggalDari = $this->selectedTanggalFilter($request->query('tanggal_dari'));
        $tanggalSampai = $this->selectedTanggalFilter($request->query('tanggal_sampai'));

        if ($tanggalDari && $tanggalSampai && $tanggalDari->gt($tanggalSampai)) {
            [$tanggalDari, $tanggalSampai] = [$tanggalSampai, $tanggalDari];
        }

        $semuaArtikel = Artikel::query()
            ->with(['kategori', 'penulis'])
            ->withCount('revisi')
            ->when($kategoriDipilih, fn (Builder $builder) => $builder->where('kategori_artikel_id', $kategoriDipilih->id))
            ->when($penulisDipilih, fn (Builder $builder) => $builder->where('penulis_id', $penulisDipilih->id))
            ->latest('updated_at')
            ->get();
        $semuaArtikel = $this->filterArtikelBerdasarkanStatusEditorial($semuaArtikel, $statusEditorialDipilih);
        $semuaArtikel = $this->filterArtikelBerdasarkanTanggalEditorial(
            $semuaArtikel,
            $basisTanggalDipilih,
            $tanggalDari?->copy()->startOfDay(),
            $tanggalSampai?->copy()->endOfDay()
        );

        $artikelDraft = $semuaArtikel
            ->filter(fn (Artikel $artikel) => $artikel->adalahDraft())
            ->values();
        $artikelTerjadwal = $semuaArtikel
            ->filter(fn (Artikel $artikel) => $artikel->sedangTerjadwal())
            ->sortBy('diterbitkan_pada')
            ->values();
        $artikelTerbitAktif = $semuaArtikel
            ->filter(fn (Artikel $artikel) => $artikel->sudahTerbitAktif())
            ->sortByDesc('diterbitkan_pada')
            ->values();
        $artikelSiapTerbit = $artikelDraft
            ->filter(fn (Artikel $artikel) => $artikel->evaluasiKesiapan()['siap_terbit'])
            ->sortByDesc('updated_at')
            ->values();
        $artikelPerluRevisi = $semuaArtikel
            ->filter(fn (Artikel $artikel) => ! $artikel->evaluasiKesiapan()['siap_terbit'])
            ->sortByDesc('updated_at')
            ->values();
        $artikelTerlambatDitinjau = $artikelDraft
            ->filter(fn (Artikel $artikel) => $artikel->updated_at?->lte(now()->subDays(7)))
            ->values();
        $notifikasiEditorial = $this->susunNotifikasiEditorial(
            $semuaArtikel,
            $artikelDraft,
            $artikelTerjadwal,
            $artikelSiapTerbit,
            $artikelPerluRevisi,
            $artikelTerlambatDitinjau
        );

        return [
            'daftarPenulis' => User::query()
                ->whereHas('artikel')
                ->orderBy('name')
                ->get(['id', 'name']),
            'daftarKategori' => KategoriArtikel::query()
                ->whereHas('artikel')
                ->orderBy('nama')
                ->get(['id', 'nama', 'slug']),
            'presetEditorialPengguna' => $request->user()
                ?->presetEditorialPengguna()
                ->latest('updated_at')
                ->get() ?? collect(),
            'opsiPresetEditorial' => $this->opsiPresetEditorial(),
            'opsiStatusEditorial' => $this->opsiStatusEditorial(),
            'presetEditorialDipilih' => $presetEditorialDipilih,
            'presetPenggunaDipilih' => $presetPenggunaDipilih,
            'opsiBasisTanggalEditorial' => $this->opsiBasisTanggalEditorial(),
            'penulisDipilih' => $penulisDipilih,
            'kategoriDipilih' => $kategoriDipilih,
            'statusEditorialDipilih' => $statusEditorialDipilih,
            'basisTanggalDipilih' => $basisTanggalDipilih,
            'tanggalDariFilter' => $tanggalDari?->format('Y-m-d'),
            'tanggalSampaiFilter' => $tanggalSampai?->format('Y-m-d'),
            'filterTanggalAktif' => $tanggalDari !== null || $tanggalSampai !== null,
            'labelFilterTanggal' => $this->labelRentangTanggalEditorial($basisTanggalDipilih, $tanggalDari, $tanggalSampai),
            'dapatSimpanPresetKustom' => $this->konfigurasiFilterEditorialSaatIni(
                $penulisDipilih,
                $kategoriDipilih,
                $statusEditorialDipilih,
                $basisTanggalDipilih,
                $tanggalDari,
                $tanggalSampai
            ) !== [],
            'filterAktif' => $penulisDipilih !== null
                || $kategoriDipilih !== null
                || $statusEditorialDipilih !== null
                || $tanggalDari !== null
                || $tanggalSampai !== null,
            'jumlahSemua' => $semuaArtikel->count(),
            'jumlahDraft' => $artikelDraft->count(),
            'jumlahTerjadwal' => $artikelTerjadwal->count(),
            'jumlahTerbitAktif' => $artikelTerbitAktif->count(),
            'jumlahSiapTerbit' => $artikelSiapTerbit->count(),
            'jumlahPerluRevisi' => $artikelPerluRevisi->count(),
            'notifikasiEditorial' => $notifikasiEditorial,
            'artikelTerjadwal' => $artikelTerjadwal->take(8),
            'artikelSiapTerbit' => $artikelSiapTerbit->take(8),
            'artikelPerluRevisi' => $artikelPerluRevisi->take(8),
            'artikelTerbitTerbaru' => $artikelTerbitAktif->take(8),
            'artikelFilterTerbaru' => $semuaArtikel
                ->sortByDesc('updated_at')
                ->take(6)
                ->values(),
        ];
    }

    private function susunNotifikasiEditorial(
        Collection $semuaArtikel,
        Collection $artikelDraft,
        Collection $artikelTerjadwal,
        Collection $artikelSiapTerbit,
        Collection $artikelPerluRevisi,
        Collection $artikelTerlambatDitinjau
    ): Collection {
        $notifikasi = collect();
        $jadwalTerdekat = $artikelTerjadwal
            ->filter(fn (Artikel $artikel) => $artikel->diterbitkan_pada?->lte(now()->addDays(2)))
            ->count();

        if ($semuaArtikel->isEmpty()) {
            return collect([
                [
                    'tipe' => 'info',
                    'judul' => 'Belum ada data editorial',
                    'pesan' => 'Filter yang dipilih belum menghasilkan artikel apa pun.',
                ],
            ]);
        }

        if ($artikelSiapTerbit->isNotEmpty()) {
            $notifikasi->push([
                'tipe' => 'success',
                'judul' => 'Siap diterbitkan',
                'pesan' => $artikelSiapTerbit->count().' artikel sudah siap terbit dan bisa diproses editor.',
            ]);
        }

        if ($jadwalTerdekat > 0) {
            $notifikasi->push([
                'tipe' => 'warning',
                'judul' => 'Jadwal terdekat',
                'pesan' => $jadwalTerdekat.' artikel akan tayang dalam 48 jam ke depan.',
            ]);
        }

        if ($artikelPerluRevisi->isNotEmpty()) {
            $notifikasi->push([
                'tipe' => 'danger',
                'judul' => 'Perlu revisi',
                'pesan' => $artikelPerluRevisi->count().' artikel masih terblokir untuk publikasi.',
            ]);
        }

        if ($artikelTerlambatDitinjau->isNotEmpty()) {
            $notifikasi->push([
                'tipe' => 'warning',
                'judul' => 'Draft perlu perhatian',
                'pesan' => $artikelTerlambatDitinjau->count().' draft belum diperbarui selama lebih dari 7 hari.',
            ]);
        }

        if ($notifikasi->isEmpty()) {
            $notifikasi->push([
                'tipe' => 'info',
                'judul' => 'Tidak ada notifikasi mendesak',
                'pesan' => 'Semua artikel pada filter ini berada dalam kondisi stabil.',
            ]);
        }

        return $notifikasi;
    }

    private function opsiStatusEditorial(): array
    {
        return [
            'draft' => 'Draft',
            'terjadwal' => 'Terjadwal',
            'siap_terbit' => 'Siap Terbit',
            'perlu_revisi' => 'Perlu Revisi',
            'diterbitkan' => 'Diterbitkan',
        ];
    }

    private function opsiPresetEditorial(): array
    {
        $sekarang = now();
        $mulaiMinggu = $sekarang->copy()->startOfWeek(Carbon::MONDAY);
        $akhirMinggu = $sekarang->copy()->endOfWeek(Carbon::SUNDAY);

        return [
            'jadwal_minggu_ini' => [
                'label' => 'Jadwal Minggu Ini',
                'deskripsi' => 'Artikel terjadwal yang akan tayang pada minggu berjalan.',
                'query' => [
                    'preset_editorial' => 'jadwal_minggu_ini',
                    'status_editorial' => 'terjadwal',
                    'basis_tanggal' => 'jadwal_terbit',
                    'tanggal_dari' => $mulaiMinggu->format('Y-m-d'),
                    'tanggal_sampai' => $akhirMinggu->format('Y-m-d'),
                ],
            ],
            'draft_lama' => [
                'label' => 'Draft Lama',
                'deskripsi' => 'Draft yang terakhir diperbarui lebih dari 7 hari lalu.',
                'query' => [
                    'preset_editorial' => 'draft_lama',
                    'status_editorial' => 'draft',
                    'basis_tanggal' => 'diperbarui',
                    'tanggal_sampai' => $sekarang->copy()->subDays(7)->format('Y-m-d'),
                ],
            ],
            'siap_terbit_hari_ini' => [
                'label' => 'Siap Terbit Hari Ini',
                'deskripsi' => 'Draft siap terbit yang diperbarui pada hari ini.',
                'query' => [
                    'preset_editorial' => 'siap_terbit_hari_ini',
                    'status_editorial' => 'siap_terbit',
                    'basis_tanggal' => 'diperbarui',
                    'tanggal_dari' => $sekarang->copy()->format('Y-m-d'),
                    'tanggal_sampai' => $sekarang->copy()->format('Y-m-d'),
                ],
            ],
        ];
    }

    private function opsiBasisTanggalEditorial(): array
    {
        return [
            'diperbarui' => 'Terakhir Diperbarui',
            'jadwal_terbit' => 'Jadwal Terbit',
        ];
    }

    private function filterArtikelBerdasarkanStatusEditorial(Collection $artikel, ?string $status): Collection
    {
        if ($status === null) {
            return $artikel->values();
        }

        return $artikel
            ->filter(function (Artikel $item) use ($status): bool {
                return match ($status) {
                    'draft' => $item->adalahDraft(),
                    'terjadwal' => $item->sedangTerjadwal(),
                    'siap_terbit' => $item->adalahDraft() && $item->evaluasiKesiapan()['siap_terbit'],
                    'perlu_revisi' => ! $item->evaluasiKesiapan()['siap_terbit'],
                    'diterbitkan' => $item->sudahTerbitAktif(),
                    default => true,
                };
            })
            ->values();
    }

    private function filterArtikelBerdasarkanTanggalEditorial(
        Collection $artikel,
        string $basisTanggal,
        ?Carbon $tanggalDari,
        ?Carbon $tanggalSampai
    ): Collection {
        if ($tanggalDari === null && $tanggalSampai === null) {
            return $artikel->values();
        }

        $fieldTanggal = $basisTanggal === 'jadwal_terbit' ? 'diterbitkan_pada' : 'updated_at';

        return $artikel
            ->filter(function (Artikel $item) use ($fieldTanggal, $tanggalDari, $tanggalSampai): bool {
                $nilaiTanggal = $item->{$fieldTanggal};

                if (! $nilaiTanggal) {
                    return false;
                }

                if ($tanggalDari && $nilaiTanggal->lt($tanggalDari)) {
                    return false;
                }

                if ($tanggalSampai && $nilaiTanggal->gt($tanggalSampai)) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    private function labelRentangTanggalEditorial(string $basisTanggal, ?Carbon $tanggalDari, ?Carbon $tanggalSampai): ?string
    {
        if ($tanggalDari === null && $tanggalSampai === null) {
            return null;
        }

        $labelBasis = $this->opsiBasisTanggalEditorial()[$basisTanggal] ?? 'Tanggal';

        if ($tanggalDari && $tanggalSampai) {
            return $labelBasis.': '.$tanggalDari->translatedFormat('d M Y').' s/d '.$tanggalSampai->translatedFormat('d M Y');
        }

        if ($tanggalDari) {
            return $labelBasis.': mulai '.$tanggalDari->translatedFormat('d M Y');
        }

        return $labelBasis.': sampai '.$tanggalSampai?->translatedFormat('d M Y');
    }

    private function konfigurasiFilterEditorialSaatIni(
        ?User $penulisDipilih,
        ?KategoriArtikel $kategoriDipilih,
        ?string $statusEditorialDipilih,
        string $basisTanggalDipilih,
        ?Carbon $tanggalDari,
        ?Carbon $tanggalSampai
    ): array {
        return $this->konfigurasiFilterEditorialDariInput([
            'penulis' => $penulisDipilih?->id,
            'kategori' => $kategoriDipilih?->id,
            'status_editorial' => $statusEditorialDipilih,
            'basis_tanggal' => $basisTanggalDipilih,
            'tanggal_dari' => $tanggalDari?->format('Y-m-d'),
            'tanggal_sampai' => $tanggalSampai?->format('Y-m-d'),
        ]);
    }

    private function konfigurasiFilterEditorialDariInput(array $input): array
    {
        $konfigurasi = [];
        $penulis = trim((string) ($input['penulis'] ?? ''));
        $kategori = trim((string) ($input['kategori'] ?? ''));
        $statusEditorial = trim((string) ($input['status_editorial'] ?? ''));
        $tanggalDari = trim((string) ($input['tanggal_dari'] ?? ''));
        $tanggalSampai = trim((string) ($input['tanggal_sampai'] ?? ''));
        $filterTanggalAktif = $tanggalDari !== '' || $tanggalSampai !== '';

        if ($penulis !== '') {
            $konfigurasi['penulis'] = $penulis;
        }

        if ($kategori !== '') {
            $konfigurasi['kategori'] = $kategori;
        }

        if ($statusEditorial !== '') {
            $konfigurasi['status_editorial'] = $statusEditorial;
        }

        if ($filterTanggalAktif) {
            $konfigurasi['basis_tanggal'] = in_array(($input['basis_tanggal'] ?? null), array_keys($this->opsiBasisTanggalEditorial()), true)
                ? (string) $input['basis_tanggal']
                : 'diperbarui';

            if ($tanggalDari !== '') {
                $konfigurasi['tanggal_dari'] = $tanggalDari;
            }

            if ($tanggalSampai !== '') {
                $konfigurasi['tanggal_sampai'] = $tanggalSampai;
            }
        }

        return $konfigurasi;
    }

    private function queryEditorialAktifDariRequest(Request $request): array
    {
        return array_filter([
            'preset_editorial' => $request->query('preset_editorial'),
            'preset_pengguna' => $request->query('preset_pengguna'),
            'penulis' => $request->query('penulis'),
            'kategori' => $request->query('kategori'),
            'status_editorial' => $request->query('status_editorial'),
            'basis_tanggal' => $request->query('basis_tanggal'),
            'tanggal_dari' => $request->query('tanggal_dari'),
            'tanggal_sampai' => $request->query('tanggal_sampai'),
        ], fn ($nilai) => filled($nilai));
    }

    private function ringkasanAuditArtikel(Artikel $artikel): array
    {
        $artikel->refresh();

        return [
            'judul' => $artikel->judul,
            'slug' => $artikel->slug,
            'kategori_artikel_id' => $artikel->kategori_artikel_id,
            'penulis_id' => $artikel->penulis_id,
            'tingkat_keahlian' => $artikel->tingkat_keahlian,
            'sudah_diterbitkan' => (bool) $artikel->sudah_diterbitkan,
            'status_publikasi' => $artikel->labelStatusPublikasi(),
            'diterbitkan_pada' => $artikel->diterbitkan_pada?->toDateTimeString(),
            'jumlah_referensi' => count($artikel->sumber_referensi ?? []),
            'punya_gambar_unggulan' => filled($artikel->gambar_unggulan_path),
            'skor_kesiapan' => $artikel->evaluasiKesiapan()['skor'],
        ];
    }
}
