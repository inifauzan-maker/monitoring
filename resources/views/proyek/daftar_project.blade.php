@extends('tataletak.aplikasi')

@section('judul_halaman', 'Daftar Project')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Daftar Project')
@section('deskripsi_halaman', 'Kelola project, PIC, alur kerja, SOP, evaluasi awal, dan target penyelesaian dalam satu pusat kerja.')

@section('aksi_halaman')
    @if ($isSuperadmin)
        <a href="#form-project" class="btn btn-primary">
            {{ $editItem ? 'Ubah Project' : 'Tambah Project' }}
        </a>
    @endif
@endsection

@section('konten')
    @php
        $totalProject = $ringkasan->count();
        $projectAktif = $ringkasan->whereIn('status_project', ['perencanaan', 'berjalan'])->count();
        $projectSelesai = $ringkasan->where('status_project', 'selesai')->count();
        $rataRataProgres = $ringkasan->count() > 0 ? (int) round($ringkasan->avg(fn ($item) => $item->persentaseProgres())) : 0;
        $parameterFilter = [];

        if ($selectedStatusProject) {
            $parameterFilter['status_project'] = $selectedStatusProject;
        }

        if ($selectedPenanggungJawab) {
            $parameterFilter['penanggung_jawab_id'] = $selectedPenanggungJawab;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Project</div><div class="h1 mb-0 mt-2">{{ number_format($totalProject, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Project Aktif</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($projectAktif, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Project Selesai</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($projectSelesai, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Rata-rata Progres</div><div class="h1 mb-0 mt-2">{{ $rataRataProgres }}%</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Project</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="status_project">Status Project</label>
                            <select class="form-select" id="status_project" name="status_project">
                                <option value="">Semua Status</option>
                                @foreach ($statusProjectOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatusProject === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="penanggung_jawab_id">Penanggung Jawab</label>
                            <select class="form-select" id="penanggung_jawab_id" name="penanggung_jawab_id">
                                <option value="">Semua PIC</option>
                                @foreach ($penanggungJawabOptions as $user)
                                    <option value="{{ $user->id }}" @selected($selectedPenanggungJawab === $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($parameterFilter)
                                    <a href="{{ route('proyek.daftar_project') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-project">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">{{ $editItem ? 'Ubah Project' : 'Tambah Project' }}</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('proyek.update', $editItem) : route('proyek.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            @if ($selectedStatusProject)
                                <input type="hidden" name="filter_status_project" value="{{ $selectedStatusProject }}">
                            @endif
                            @if ($selectedPenanggungJawab)
                                <input type="hidden" name="filter_penanggung_jawab" value="{{ $selectedPenanggungJawab }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label" for="kode_project">Kode Project</label>
                                    <input type="text" class="form-control" id="kode_project" name="kode_project" value="{{ old('kode_project', $editItem?->kode_project) }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="nama_project">Nama Project</label>
                                    <input type="text" class="form-control" id="nama_project" name="nama_project" value="{{ old('nama_project', $editItem?->nama_project) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="klien">Klien</label>
                                    <input type="text" class="form-control" id="klien" name="klien" value="{{ old('klien', $editItem?->klien) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="status_project_form">Status Project</label>
                                    <select class="form-select" id="status_project_form" name="status_project" required>
                                        @foreach ($statusProjectOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(old('status_project', $editItem?->status_project ?? 'perencanaan') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="prioritas_project">Prioritas</label>
                                    <select class="form-select" id="prioritas_project" name="prioritas_project" required>
                                        @foreach ($prioritasProjectOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(old('prioritas_project', $editItem?->prioritas_project ?? 'sedang') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="penanggung_jawab_id_form">Penanggung Jawab</label>
                                    <select class="form-select" id="penanggung_jawab_id_form" name="penanggung_jawab_id">
                                        <option value="">Pilih PIC</option>
                                        @foreach ($penanggungJawabOptions as $user)
                                            <option value="{{ $user->id }}" @selected((string) old('penanggung_jawab_id', $editItem?->penanggung_jawab_id) === (string) $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $editItem?->tanggal_mulai?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="tanggal_target_selesai">Target Selesai</label>
                                    <input type="date" class="form-control" id="tanggal_target_selesai" name="tanggal_target_selesai" value="{{ old('tanggal_target_selesai', $editItem?->tanggal_target_selesai?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="skor_evaluasi">Skor Evaluasi</label>
                                    <input type="number" class="form-control" id="skor_evaluasi" name="skor_evaluasi" min="0" max="100" value="{{ old('skor_evaluasi', $editItem?->skor_evaluasi) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="deskripsi_project">Deskripsi Project</label>
                                    <textarea class="form-control" id="deskripsi_project" name="deskripsi_project" rows="3">{{ old('deskripsi_project', $editItem?->deskripsi_project) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="alur_kerja">Alur Kerja</label>
                                    <textarea class="form-control" id="alur_kerja" name="alur_kerja" rows="4">{{ old('alur_kerja', $editItem?->alur_kerja) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="sop_ringkas">SOP Ringkas</label>
                                    <textarea class="form-control" id="sop_ringkas" name="sop_ringkas" rows="4">{{ old('sop_ringkas', $editItem?->sop_ringkas) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="catatan_evaluasi">Catatan Evaluasi</label>
                                    <textarea class="form-control" id="catatan_evaluasi" name="catatan_evaluasi" rows="3">{{ old('catatan_evaluasi', $editItem?->catatan_evaluasi) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">{{ $editItem ? 'Simpan Perubahan' : 'Simpan Project' }}</button>
                                @if ($editItem)
                                    <a href="{{ route('proyek.daftar_project', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Project</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Prioritas</th>
                                <th>Tanggal</th>
                                <th>Tugas</th>
                                <th>Progres</th>
                                @if ($isSuperadmin)
                                    <th class="w-1">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php($progres = $item->persentaseProgres())
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_project }}</div>
                                        <div class="text-secondary small">{{ $item->kode_project }}{{ $item->klien ? ' • '.$item->klien : '' }}</div>
                                    </td>
                                    <td>{{ $item->penanggungJawab?->name ?: '-' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusProject() }}">{{ $item->labelStatusProject() }}</span></td>
                                    <td><span class="badge {{ $item->kelasBadgePrioritasProject() }}">{{ $item->labelPrioritasProject() }}</span></td>
                                    <td>
                                        <div>{{ $item->tanggal_mulai?->format('d M Y') ?: '-' }}</div>
                                        <div class="text-secondary small">Target: {{ $item->tanggal_target_selesai?->format('d M Y') ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->totalTugasSelesai() }}/{{ $item->totalTugas() }}</div>
                                        <a href="{{ route('proyek.detail_tugas', ['proyek_id' => $item->id]) }}" class="small text-decoration-none">Lihat tugas</a>
                                    </td>
                                    <td style="min-width: 13rem;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $progres }}%</span>
                                            <span class="text-secondary">Skor: {{ $item->skor_evaluasi ?? '-' }}</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: {{ $progres }}%"></div>
                                        </div>
                                    </td>
                                    @if ($isSuperadmin)
                                        <td>
                                            <div class="btn-list justify-content-end flex-nowrap">
                                                <a href="{{ route('proyek.edit', ['proyek' => $item] + $parameterFilter) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <form method="POST" action="{{ route('proyek.destroy', $item) }}" onsubmit="return confirm('Hapus project ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    @if ($selectedStatusProject)
                                                        <input type="hidden" name="filter_status_project" value="{{ $selectedStatusProject }}">
                                                    @endif
                                                    @if ($selectedPenanggungJawab)
                                                        <input type="hidden" name="filter_penanggung_jawab" value="{{ $selectedPenanggungJawab }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperadmin ? 8 : 7 }}" class="text-center text-secondary">Belum ada data project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $items->links() }}</div>
            </div>
        </div>
    </div>
@endsection
