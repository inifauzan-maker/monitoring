<?php

namespace App\Http\Controllers;

use App\Models\Proyek;
use App\Models\TugasProyek;
use App\Models\User;
use App\Support\PencatatLogAktivitas;
use App\Support\PencatatNotifikasi;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProyekController extends Controller
{
    public function daftarProject(Request $request): View
    {
        return $this->renderDaftarProject($request);
    }

    public function edit(Request $request, Proyek $proyek): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderDaftarProject($request, $proyek);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->normalizeProyekPayload($this->validatedProyek($request));
        $data['dibuat_oleh'] = $request->user()->id;

        $proyek = Proyek::create($data);
        PencatatLogAktivitas::catat(
            $request,
            'proyek',
            'tambah',
            'Project baru ditambahkan.',
            $proyek,
            [
                'kode_project' => $proyek->kode_project,
                'status_project' => $proyek->status_project,
                'prioritas_project' => $proyek->prioritas_project,
            ],
        );
        $this->kirimNotifikasiProjectBaru($proyek->fresh(['penanggungJawab']), $request->user());

        return redirect()
            ->route('proyek.daftar_project', $this->projectFilterQuery(
                $request->input('filter_status_project'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Data project berhasil ditambahkan.');
    }

    public function update(Request $request, Proyek $proyek): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $snapshotSebelum = [
            'nama_project' => $proyek->nama_project,
            'status_project' => $proyek->status_project,
            'prioritas_project' => $proyek->prioritas_project,
            'penanggung_jawab_id' => $proyek->penanggung_jawab_id,
        ];
        $data = $this->normalizeProyekPayload($this->validatedProyek($request, $proyek), $proyek);

        $proyek->update($data);
        $proyek->loadMissing('penanggungJawab');
        $snapshotSesudah = [
            'nama_project' => $proyek->nama_project,
            'status_project' => $proyek->status_project,
            'prioritas_project' => $proyek->prioritas_project,
            'penanggung_jawab_id' => $proyek->penanggung_jawab_id,
        ];
        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();
        PencatatLogAktivitas::catat(
            $request,
            'proyek',
            'ubah',
            'Data project diperbarui.',
            $proyek,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );
        $this->kirimNotifikasiPerubahanProject(
            $proyek,
            $request->user(),
            $snapshotSebelum,
            $snapshotSesudah,
        );

        return redirect()
            ->route('proyek.daftar_project', $this->projectFilterQuery(
                $request->input('filter_status_project'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Data project berhasil diperbarui.');
    }

    public function destroy(Request $request, Proyek $proyek): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        PencatatLogAktivitas::catat(
            $request,
            'proyek',
            'hapus',
            'Project dihapus.',
            $proyek,
            [
                'kode_project' => $proyek->kode_project,
                'nama_project' => $proyek->nama_project,
            ],
        );

        $proyek->delete();

        return redirect()
            ->route('proyek.daftar_project', $this->projectFilterQuery(
                $request->input('filter_status_project'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Data project berhasil dihapus.');
    }

    public function detailTugas(Request $request): View
    {
        return $this->renderDetailTugas($request);
    }

    public function editTugas(Request $request, TugasProyek $tugasProyek): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderDetailTugas($request, $tugasProyek);
    }

    public function storeTugas(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->normalizeTugasPayload($this->validatedTugas($request));
        $data['urutan'] = $data['urutan'] ?? ((TugasProyek::where('proyek_id', $data['proyek_id'])->max('urutan') ?? -1) + 1);

        $tugas = TugasProyek::create($data);
        $tugas->loadMissing(['proyek', 'penanggungJawab']);
        $this->catatHistoriTugas(
            $tugas,
            $request->user(),
            null,
            $tugas->status_tugas,
            null,
            $tugas->persentase_progres,
            'Tugas dibuat.',
        );
        PencatatLogAktivitas::catat(
            $request,
            'tugas_proyek',
            'tambah',
            'Tugas project baru ditambahkan.',
            $tugas,
            [
                'proyek_id' => $tugas->proyek_id,
                'status_tugas' => $tugas->status_tugas,
                'persentase_progres' => $tugas->persentase_progres,
            ],
        );
        $this->kirimNotifikasiTugasBaru($tugas, $request->user());

        return redirect()
            ->route('proyek.detail_tugas', $this->taskFilterQuery(
                $request->input('filter_proyek_id'),
                $request->input('filter_status_tugas'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Tugas project berhasil ditambahkan.');
    }

    public function updateTugas(Request $request, TugasProyek $tugasProyek): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $statusSebelum = $tugasProyek->status_tugas;
        $progresSebelum = $tugasProyek->persentase_progres;
        $penanggungJawabSebelumId = $tugasProyek->penanggung_jawab_id;
        $data = $this->normalizeTugasPayload($this->validatedTugas($request), $tugasProyek);
        $data['urutan'] = $data['urutan'] ?? $tugasProyek->urutan;

        $tugasProyek->update($data);
        $tugasProyek->loadMissing(['proyek', 'penanggungJawab']);
        $this->catatHistoriTugasJikaBerubah(
            $tugasProyek,
            $request->user(),
            $statusSebelum,
            $tugasProyek->status_tugas,
            $progresSebelum,
            $tugasProyek->persentase_progres,
            'Tugas diperbarui dari formulir lengkap.',
        );
        PencatatLogAktivitas::catat(
            $request,
            'tugas_proyek',
            'ubah',
            'Tugas project diperbarui.',
            $tugasProyek,
            [
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $tugasProyek->status_tugas,
                'progres_sebelum' => $progresSebelum,
                'progres_sesudah' => $tugasProyek->persentase_progres,
            ],
        );
        $this->kirimNotifikasiPerubahanTugas(
            $tugasProyek,
            $request->user(),
            $statusSebelum,
            $progresSebelum,
            $penanggungJawabSebelumId,
        );

        return redirect()
            ->route('proyek.detail_tugas', $this->taskFilterQuery(
                $request->input('filter_proyek_id'),
                $request->input('filter_status_tugas'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Tugas project berhasil diperbarui.');
    }

    public function perbaruiStatusTugasCepat(Request $request, TugasProyek $tugasProyek): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $statusSebelum = $tugasProyek->status_tugas;
        $progresSebelum = $tugasProyek->persentase_progres;
        $penanggungJawabSebelumId = $tugasProyek->penanggung_jawab_id;
        $data = $request->validate([
            'status_tugas' => ['required', Rule::in(array_keys(TugasProyek::statusOptions()))],
            'persentase_progres' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [], [
            'status_tugas' => 'status tugas',
            'persentase_progres' => 'persentase progres',
        ]);

        $payload = $this->normalizeTugasCepatPayload($data, $tugasProyek);

        $tugasProyek->update($payload);
        $tugasProyek->loadMissing(['proyek', 'penanggungJawab']);
        $this->catatHistoriTugasJikaBerubah(
            $tugasProyek,
            $request->user(),
            $statusSebelum,
            $tugasProyek->status_tugas,
            $progresSebelum,
            $tugasProyek->persentase_progres,
            'Status tugas diperbarui dari board.',
        );
        PencatatLogAktivitas::catat(
            $request,
            'tugas_proyek',
            'status_cepat',
            'Status tugas diperbarui cepat dari board.',
            $tugasProyek,
            [
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $tugasProyek->status_tugas,
                'progres_sebelum' => $progresSebelum,
                'progres_sesudah' => $tugasProyek->persentase_progres,
            ],
        );
        $this->kirimNotifikasiPerubahanTugas(
            $tugasProyek,
            $request->user(),
            $statusSebelum,
            $progresSebelum,
            $penanggungJawabSebelumId,
        );

        return redirect()
            ->route('proyek.detail_tugas', $this->taskFilterQuery(
                $request->input('filter_proyek_id'),
                $request->input('filter_status_tugas'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Status tugas berhasil diperbarui dari board.');
    }

    public function destroyTugas(Request $request, TugasProyek $tugasProyek): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        PencatatLogAktivitas::catat(
            $request,
            'tugas_proyek',
            'hapus',
            'Tugas project dihapus.',
            $tugasProyek,
            [
                'judul_tugas' => $tugasProyek->judul_tugas,
                'proyek_id' => $tugasProyek->proyek_id,
            ],
        );

        $tugasProyek->delete();

        return redirect()
            ->route('proyek.detail_tugas', $this->taskFilterQuery(
                $request->input('filter_proyek_id'),
                $request->input('filter_status_tugas'),
                $request->input('filter_penanggung_jawab'),
            ))
            ->with('status', 'Tugas project berhasil dihapus.');
    }

    public function historiTugas(Request $request, TugasProyek $tugasProyek): View
    {
        $histori = $tugasProyek->historiProgres()
            ->with('pencatat')
            ->latest()
            ->paginate(20);

        return view('proyek.histori_tugas', [
            'tugas' => $tugasProyek->load(['proyek', 'penanggungJawab']),
            'histori' => $histori,
        ]);
    }

    public function alurSop(Request $request): View
    {
        $items = Proyek::query()
            ->with('penanggungJawab')
            ->orderBy('nama_project')
            ->get();

        return view('proyek.alur_sop', [
            'items' => $items,
        ]);
    }

    public function penanggungJawab(Request $request): View
    {
        $users = User::query()
            ->withCount([
                'proyekDitanggungjawabi',
                'tugasProyekDitanggungjawabi',
                'tugasProyekDitanggungjawabi as tugas_selesai_count' => fn (Builder $query) => $query->where('status_tugas', 'selesai'),
                'tugasProyekDitanggungjawabi as tugas_tertunda_count' => fn (Builder $query) => $query->where('status_tugas', 'tertunda'),
            ])
            ->with([
                'tugasProyekDitanggungjawabi' => fn ($query) => $query->select('id', 'penanggung_jawab_id', 'persentase_progres'),
            ])
            ->get()
            ->filter(fn (User $user) => $user->proyek_ditanggungjawabi_count > 0 || $user->tugas_proyek_ditanggungjawabi_count > 0)
            ->map(function (User $user) {
                $rataRataProgres = $user->tugasProyekDitanggungjawabi->count() > 0
                    ? (int) round($user->tugasProyekDitanggungjawabi->avg('persentase_progres'))
                    : 0;

                return [
                    'user' => $user,
                    'total_project' => (int) $user->proyek_ditanggungjawabi_count,
                    'total_tugas' => (int) $user->tugas_proyek_ditanggungjawabi_count,
                    'tugas_selesai' => (int) $user->tugas_selesai_count,
                    'tugas_tertunda' => (int) $user->tugas_tertunda_count,
                    'rata_rata_progres' => $rataRataProgres,
                ];
            })
            ->sortByDesc('total_tugas')
            ->values();

        return view('proyek.penanggung_jawab', [
            'items' => $users,
        ]);
    }

    public function progres(Request $request): View
    {
        $selectedStatusProject = $this->selectedStatusProject($request->query('status_project'));
        $items = $this->queryProyek($selectedStatusProject, null)
            ->with(['penanggungJawab'])
            ->withCount([
                'tugas',
                'tugas as tugas_selesai_count' => fn (Builder $query) => $query->where('status_tugas', 'selesai'),
            ])
            ->withAvg('tugas as rata_rata_progres', 'persentase_progres')
            ->orderByDesc('created_at')
            ->get();
        $tugasTerlambat = TugasProyek::query()
            ->where('status_tugas', '!=', 'selesai')
            ->whereDate('tanggal_target', '<', now()->toDateString())
            ->count();

        return view('proyek.progres', [
            'items' => $items,
            'selectedStatusProject' => $selectedStatusProject,
            'statusProjectOptions' => Proyek::statusOptions(),
            'tugasTerlambat' => $tugasTerlambat,
        ]);
    }

    public function evaluasi(Request $request): View
    {
        $items = Proyek::query()
            ->with('penanggungJawab')
            ->orderByDesc('updated_at')
            ->get();

        return view('proyek.evaluasi', [
            'items' => $items,
        ]);
    }

    public function pelaporan(Request $request): View
    {
        $semuaProject = Proyek::query()
            ->withCount([
                'tugas',
                'tugas as tugas_selesai_count' => fn (Builder $query) => $query->where('status_tugas', 'selesai'),
            ])
            ->withAvg('tugas as rata_rata_progres', 'persentase_progres')
            ->orderByDesc('created_at')
            ->get();

        $ringkasanStatusProject = collect(Proyek::statusOptions())
            ->map(function (string $label, string $status) use ($semuaProject) {
                return [
                    'label' => $label,
                    'total' => $semuaProject->where('status_project', $status)->count(),
                ];
            });

        $ringkasanStatusTugas = collect(TugasProyek::statusOptions())
            ->map(function (string $label, string $status) {
                return [
                    'label' => $label,
                    'total' => TugasProyek::query()->where('status_tugas', $status)->count(),
                ];
            });

        $tugasTerbaru = TugasProyek::query()
            ->with(['proyek', 'penanggungJawab'])
            ->latest()
            ->take(10)
            ->get();

        return view('proyek.pelaporan', [
            'semuaProject' => $semuaProject,
            'ringkasanStatusProject' => $ringkasanStatusProject,
            'ringkasanStatusTugas' => $ringkasanStatusTugas,
            'tugasTerbaru' => $tugasTerbaru,
        ]);
    }

    private function renderDaftarProject(Request $request, ?Proyek $editItem = null): View
    {
        $selectedStatusProject = $this->selectedStatusProject($request->query('status_project'));
        $selectedPenanggungJawab = $this->selectedPenanggungJawab($request->query('penanggung_jawab_id'));
        $baseQuery = $this->queryProyek($selectedStatusProject, $selectedPenanggungJawab);

        $items = (clone $baseQuery)
            ->with(['penanggungJawab'])
            ->withCount([
                'tugas',
                'tugas as tugas_selesai_count' => fn (Builder $query) => $query->where('status_tugas', 'selesai'),
            ])
            ->withAvg('tugas as rata_rata_progres', 'persentase_progres')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $ringkasan = (clone $baseQuery)
            ->withAvg('tugas as rata_rata_progres', 'persentase_progres')
            ->get();

        return view('proyek.daftar_project', [
            'items' => $items,
            'ringkasan' => $ringkasan,
            'selectedStatusProject' => $selectedStatusProject,
            'selectedPenanggungJawab' => $selectedPenanggungJawab,
            'statusProjectOptions' => Proyek::statusOptions(),
            'prioritasProjectOptions' => Proyek::prioritasOptions(),
            'penanggungJawabOptions' => $this->userOptions(),
            'editItem' => $editItem,
            'isSuperadmin' => $request->user()?->adalahSuperadmin() ?? false,
        ]);
    }

    private function renderDetailTugas(Request $request, ?TugasProyek $editItem = null): View
    {
        $selectedProyekId = $this->selectedProyekId($request->query('proyek_id'));
        $selectedStatusTugas = $this->selectedStatusTugas($request->query('status_tugas'));
        $selectedPenanggungJawab = $this->selectedPenanggungJawab($request->query('penanggung_jawab_id'));
        $baseQuery = $this->queryTugas($selectedProyekId, $selectedStatusTugas, $selectedPenanggungJawab);

        $items = (clone $baseQuery)
            ->with(['proyek', 'penanggungJawab'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = (clone $baseQuery)->get();
        $boardItems = (clone $baseQuery)
            ->with(['proyek', 'penanggungJawab'])
            ->orderBy('urutan')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('status_tugas');
        $boardTugas = collect(TugasProyek::statusOptions())
            ->mapWithKeys(fn (string $label, string $status) => [$status => $boardItems->get($status, collect())]);

        return view('proyek.detail_tugas', [
            'items' => $items,
            'boardTugas' => $boardTugas,
            'ringkasan' => $ringkasan,
            'selectedProyekId' => $selectedProyekId,
            'selectedStatusTugas' => $selectedStatusTugas,
            'selectedPenanggungJawab' => $selectedPenanggungJawab,
            'proyekOptions' => Proyek::query()->orderBy('nama_project')->get(),
            'penanggungJawabOptions' => $this->userOptions(),
            'statusTugasOptions' => TugasProyek::statusOptions(),
            'prioritasTugasOptions' => TugasProyek::prioritasOptions(),
            'editItem' => $editItem,
            'isSuperadmin' => $request->user()?->adalahSuperadmin() ?? false,
        ]);
    }

    private function queryProyek(?string $statusProject, ?int $penanggungJawabId): Builder
    {
        return Proyek::query()
            ->when($statusProject, fn (Builder $query) => $query->where('status_project', $statusProject))
            ->when($penanggungJawabId, fn (Builder $query) => $query->where('penanggung_jawab_id', $penanggungJawabId));
    }

    private function queryTugas(?int $proyekId, ?string $statusTugas, ?int $penanggungJawabId): Builder
    {
        return TugasProyek::query()
            ->when($proyekId, fn (Builder $query) => $query->where('proyek_id', $proyekId))
            ->when($statusTugas, fn (Builder $query) => $query->where('status_tugas', $statusTugas))
            ->when($penanggungJawabId, fn (Builder $query) => $query->where('penanggung_jawab_id', $penanggungJawabId));
    }

    private function validatedProyek(Request $request, ?Proyek $proyek = null): array
    {
        return $request->validate([
            'kode_project' => [
                'required',
                'string',
                'max:50',
                Rule::unique('proyek', 'kode_project')->ignore($proyek?->id),
            ],
            'nama_project' => ['required', 'string', 'max:150'],
            'klien' => ['nullable', 'string', 'max:150'],
            'status_project' => ['required', Rule::in(array_keys(Proyek::statusOptions()))],
            'prioritas_project' => ['required', Rule::in(array_keys(Proyek::prioritasOptions()))],
            'penanggung_jawab_id' => ['nullable', 'exists:users,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_target_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'deskripsi_project' => ['nullable', 'string'],
            'alur_kerja' => ['nullable', 'string'],
            'sop_ringkas' => ['nullable', 'string'],
            'skor_evaluasi' => ['nullable', 'integer', 'min:0', 'max:100'],
            'catatan_evaluasi' => ['nullable', 'string'],
        ], [], [
            'kode_project' => 'kode project',
            'nama_project' => 'nama project',
            'klien' => 'klien',
            'status_project' => 'status project',
            'prioritas_project' => 'prioritas project',
            'penanggung_jawab_id' => 'penanggung jawab',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_target_selesai' => 'target selesai',
            'deskripsi_project' => 'deskripsi project',
            'alur_kerja' => 'alur kerja',
            'sop_ringkas' => 'SOP ringkas',
            'skor_evaluasi' => 'skor evaluasi',
            'catatan_evaluasi' => 'catatan evaluasi',
        ]);
    }

    private function validatedTugas(Request $request): array
    {
        return $request->validate([
            'proyek_id' => ['required', 'exists:proyek,id'],
            'judul_tugas' => ['required', 'string', 'max:180'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'status_tugas' => ['required', Rule::in(array_keys(TugasProyek::statusOptions()))],
            'prioritas_tugas' => ['required', Rule::in(array_keys(TugasProyek::prioritasOptions()))],
            'persentase_progres' => ['required', 'integer', 'min:0', 'max:100'],
            'penanggung_jawab_id' => ['nullable', 'exists:users,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_target' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan_tugas' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [], [
            'proyek_id' => 'project',
            'judul_tugas' => 'judul tugas',
            'deskripsi_tugas' => 'deskripsi tugas',
            'status_tugas' => 'status tugas',
            'prioritas_tugas' => 'prioritas tugas',
            'persentase_progres' => 'persentase progres',
            'penanggung_jawab_id' => 'penanggung jawab',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_target' => 'tanggal target',
            'catatan_tugas' => 'catatan tugas',
            'urutan' => 'urutan',
        ]);
    }

    private function normalizeProyekPayload(array $data, ?Proyek $existing = null): array
    {
        if (($data['status_project'] ?? null) === 'selesai') {
            $data['tanggal_selesai'] = $existing?->tanggal_selesai ?? now()->toDateString();
        } else {
            $data['tanggal_selesai'] = null;
        }

        return $data;
    }

    private function normalizeTugasPayload(array $data, ?TugasProyek $existing = null): array
    {
        $persentase = (int) ($data['persentase_progres'] ?? 0);

        if (($data['status_tugas'] ?? null) === 'selesai' || $persentase >= 100) {
            $data['status_tugas'] = 'selesai';
            $data['persentase_progres'] = 100;
            $data['tanggal_selesai'] = $existing?->tanggal_selesai ?? now()->toDateString();

            return $data;
        }

        $data['persentase_progres'] = $persentase;
        $data['tanggal_selesai'] = null;

        return $data;
    }

    private function normalizeTugasCepatPayload(array $data, TugasProyek $existing): array
    {
        $data['persentase_progres'] = array_key_exists('persentase_progres', $data) && $data['persentase_progres'] !== null
            ? (int) $data['persentase_progres']
            : (int) $existing->persentase_progres;

        if (($data['status_tugas'] ?? null) === 'belum_mulai') {
            $data['persentase_progres'] = 0;
            $data['tanggal_mulai'] = null;
        }

        if (($data['status_tugas'] ?? null) === 'berjalan' && $data['persentase_progres'] === 0) {
            $data['persentase_progres'] = 25;
        }

        if (($data['status_tugas'] ?? null) === 'review' && $data['persentase_progres'] < 75) {
            $data['persentase_progres'] = 75;
        }

        if (in_array($data['status_tugas'] ?? null, ['berjalan', 'review', 'selesai'], true) && $existing->tanggal_mulai === null) {
            $data['tanggal_mulai'] = now()->toDateString();
        }

        return $this->normalizeTugasPayload($data, $existing);
    }

    private function catatHistoriTugas(
        TugasProyek $tugasProyek,
        ?User $pencatat,
        ?string $statusSebelum,
        string $statusSesudah,
        ?int $progresSebelum,
        int $progresSesudah,
        string $catatanHistori,
    ): void {
        $tugasProyek->historiProgres()->create([
            'user_id' => $pencatat?->id,
            'status_sebelum' => $statusSebelum,
            'status_sesudah' => $statusSesudah,
            'progres_sebelum' => $progresSebelum,
            'progres_sesudah' => $progresSesudah,
            'catatan_histori' => $catatanHistori,
        ]);
    }

    private function catatHistoriTugasJikaBerubah(
        TugasProyek $tugasProyek,
        ?User $pencatat,
        ?string $statusSebelum,
        string $statusSesudah,
        ?int $progresSebelum,
        int $progresSesudah,
        string $catatanHistori,
    ): void {
        if ($statusSebelum === $statusSesudah && (int) $progresSebelum === $progresSesudah) {
            return;
        }

        $this->catatHistoriTugas(
            $tugasProyek,
            $pencatat,
            $statusSebelum,
            $statusSesudah,
            $progresSebelum,
            $progresSesudah,
            $catatanHistori,
        );
    }

    private function selectedStatusProject(?string $value): ?string
    {
        return in_array($value, array_keys(Proyek::statusOptions()), true) ? $value : null;
    }

    private function selectedStatusTugas(?string $value): ?string
    {
        return in_array($value, array_keys(TugasProyek::statusOptions()), true) ? $value : null;
    }

    private function selectedPenanggungJawab(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 && User::query()->whereKey($value)->exists() ? $value : null;
    }

    private function selectedProyekId(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 && Proyek::query()->whereKey($value)->exists() ? $value : null;
    }

    private function projectFilterQuery(?string $statusProject, mixed $penanggungJawabId): array
    {
        $query = [];

        if ($selectedStatusProject = $this->selectedStatusProject($statusProject)) {
            $query['status_project'] = $selectedStatusProject;
        }

        if ($selectedPenanggungJawab = $this->selectedPenanggungJawab($penanggungJawabId)) {
            $query['penanggung_jawab_id'] = $selectedPenanggungJawab;
        }

        return $query;
    }

    private function taskFilterQuery(mixed $proyekId, ?string $statusTugas, mixed $penanggungJawabId): array
    {
        $query = [];

        if ($selectedProyekId = $this->selectedProyekId($proyekId)) {
            $query['proyek_id'] = $selectedProyekId;
        }

        if ($selectedStatusTugas = $this->selectedStatusTugas($statusTugas)) {
            $query['status_tugas'] = $selectedStatusTugas;
        }

        if ($selectedPenanggungJawab = $this->selectedPenanggungJawab($penanggungJawabId)) {
            $query['penanggung_jawab_id'] = $selectedPenanggungJawab;
        }

        return $query;
    }

    private function userOptions()
    {
        return User::query()->orderBy('name')->get();
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function kirimNotifikasiProjectBaru(Proyek $proyek, ?User $aktor): void
    {
        $penerima = $proyek->penanggungJawab;

        if (! $penerima || ($aktor && $aktor->is($penerima))) {
            return;
        }

        PencatatNotifikasi::kirim(
            $penerima,
            'Anda ditunjuk sebagai PIC project',
            'Project "'.$proyek->nama_project.'" sekarang menjadi tanggung jawab Anda.',
            'info',
            route('proyek.daftar_project', ['penanggung_jawab_id' => $penerima->id]),
            [
                'modul' => 'proyek',
                'proyek_id' => $proyek->id,
                'kode_project' => $proyek->kode_project,
            ],
        );
    }

    private function kirimNotifikasiPerubahanProject(
        Proyek $proyek,
        ?User $aktor,
        array $snapshotSebelum,
        array $snapshotSesudah,
    ): void {
        $penerima = $proyek->penanggungJawab;

        if (! $penerima || ($aktor && $aktor->is($penerima))) {
            return;
        }

        $pesan = collect();

        if (($snapshotSebelum['penanggung_jawab_id'] ?? null) !== ($snapshotSesudah['penanggung_jawab_id'] ?? null)) {
            $pesan->push('Anda sekarang menjadi PIC untuk project "'.$proyek->nama_project.'".');
        }

        if (($snapshotSebelum['status_project'] ?? null) !== ($snapshotSesudah['status_project'] ?? null)) {
            $pesan->push(
                'Status project diperbarui menjadi '.$proyek->labelStatusProject().'.'
            );
        }

        if ($pesan->isEmpty()) {
            return;
        }

        PencatatNotifikasi::kirim(
            $penerima,
            'Perubahan project',
            $pesan->implode(' '),
            'info',
            route('proyek.daftar_project', ['penanggung_jawab_id' => $penerima->id]),
            [
                'modul' => 'proyek',
                'proyek_id' => $proyek->id,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );
    }

    private function kirimNotifikasiTugasBaru(TugasProyek $tugasProyek, ?User $aktor): void
    {
        $penerima = $tugasProyek->penanggungJawab;

        if (! $penerima || ($aktor && $aktor->is($penerima))) {
            return;
        }

        PencatatNotifikasi::kirim(
            $penerima,
            'Tugas baru untuk Anda',
            'Tugas "'.$tugasProyek->judul_tugas.'" pada project "'.$tugasProyek->proyek?->nama_project.'" telah ditugaskan kepada Anda.',
            'info',
            route('proyek.tugas.histori', $tugasProyek),
            [
                'modul' => 'tugas_proyek',
                'tugas_proyek_id' => $tugasProyek->id,
                'proyek_id' => $tugasProyek->proyek_id,
            ],
        );
    }

    private function kirimNotifikasiPerubahanTugas(
        TugasProyek $tugasProyek,
        ?User $aktor,
        ?string $statusSebelum,
        ?int $progresSebelum,
        ?int $penanggungJawabSebelumId,
    ): void {
        $penerima = $tugasProyek->penanggungJawab;

        if (! $penerima || ($aktor && $aktor->is($penerima))) {
            return;
        }

        $pesan = collect();

        if ($penanggungJawabSebelumId !== $tugasProyek->penanggung_jawab_id) {
            $pesan->push('Anda sekarang ditunjuk menangani tugas ini.');
        }

        if ($statusSebelum !== $tugasProyek->status_tugas) {
            $pesan->push('Status tugas diperbarui menjadi '.$tugasProyek->labelStatusTugas().'.');
        }

        if ((int) $progresSebelum !== (int) $tugasProyek->persentase_progres) {
            $pesan->push('Progres tugas saat ini '.$tugasProyek->persentase_progres.'%.');
        }

        if ($pesan->isEmpty()) {
            return;
        }

        PencatatNotifikasi::kirim(
            $penerima,
            'Pembaruan tugas project',
            'Tugas "'.$tugasProyek->judul_tugas.'" pada project "'.$tugasProyek->proyek?->nama_project.'". '.$pesan->implode(' '),
            $tugasProyek->status_tugas === 'tertunda' ? 'warning' : 'info',
            route('proyek.tugas.histori', $tugasProyek),
            [
                'modul' => 'tugas_proyek',
                'tugas_proyek_id' => $tugasProyek->id,
                'proyek_id' => $tugasProyek->proyek_id,
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $tugasProyek->status_tugas,
                'progres_sebelum' => $progresSebelum,
                'progres_sesudah' => $tugasProyek->persentase_progres,
            ],
        );
    }
}
