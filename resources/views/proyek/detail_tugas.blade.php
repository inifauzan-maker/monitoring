@extends('tataletak.aplikasi')

@section('judul_halaman', 'Detail Tugas')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Detail Tugas')
@section('deskripsi_halaman', 'Kelola rincian tugas project, siapa yang mengerjakan, target waktu, prioritas, dan progres pengerjaannya.')

@section('aksi_halaman')
    @if ($isSuperadmin)
        <a href="#form-tugas" class="btn btn-primary">
            {{ $editItem ? 'Ubah Tugas' : 'Tambah Tugas' }}
        </a>
    @endif
@endsection

@section('konten')
    @php
        $totalTugas = $ringkasan->count();
        $tugasBerjalan = $ringkasan->where('status_tugas', 'berjalan')->count();
        $tugasSelesai = $ringkasan->where('status_tugas', 'selesai')->count();
        $rataRataProgres = $ringkasan->count() > 0 ? (int) round($ringkasan->avg('persentase_progres')) : 0;
        $parameterFilter = [];

        if ($selectedProyekId) {
            $parameterFilter['proyek_id'] = $selectedProyekId;
        }

        if ($selectedStatusTugas) {
            $parameterFilter['status_tugas'] = $selectedStatusTugas;
        }

        if ($selectedPenanggungJawab) {
            $parameterFilter['penanggung_jawab_id'] = $selectedPenanggungJawab;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Tugas</div><div class="h1 mb-0 mt-2">{{ number_format($totalTugas, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Sedang Berjalan</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($tugasBerjalan, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Selesai</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($tugasSelesai, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Rata-rata Progres</div><div class="h1 mb-0 mt-2">{{ $rataRataProgres }}%</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Tugas</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="proyek_id">Project</label>
                            <select class="form-select" id="proyek_id" name="proyek_id">
                                <option value="">Semua Project</option>
                                @foreach ($proyekOptions as $proyek)
                                    <option value="{{ $proyek->id }}" @selected($selectedProyekId === $proyek->id)>{{ $proyek->nama_project }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status_tugas">Status Tugas</label>
                            <select class="form-select" id="status_tugas" name="status_tugas">
                                <option value="">Semua Status</option>
                                @foreach ($statusTugasOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatusTugas === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="penanggung_jawab_tugas">Penanggung Jawab</label>
                            <select class="form-select" id="penanggung_jawab_tugas" name="penanggung_jawab_id">
                                <option value="">Semua PIC</option>
                                @foreach ($penanggungJawabOptions as $user)
                                    <option value="{{ $user->id }}" @selected($selectedPenanggungJawab === $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($parameterFilter)
                                    <a href="{{ route('proyek.detail_tugas') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-tugas">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">{{ $editItem ? 'Ubah Tugas' : 'Tambah Tugas' }}</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('proyek.tugas.update', $editItem) : route('proyek.tugas.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            @if ($selectedProyekId)
                                <input type="hidden" name="filter_proyek_id" value="{{ $selectedProyekId }}">
                            @endif
                            @if ($selectedStatusTugas)
                                <input type="hidden" name="filter_status_tugas" value="{{ $selectedStatusTugas }}">
                            @endif
                            @if ($selectedPenanggungJawab)
                                <input type="hidden" name="filter_penanggung_jawab" value="{{ $selectedPenanggungJawab }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="proyek_id_form">Project</label>
                                    <select class="form-select" id="proyek_id_form" name="proyek_id" required>
                                        <option value="">Pilih Project</option>
                                        @foreach ($proyekOptions as $proyek)
                                            <option value="{{ $proyek->id }}" @selected((string) old('proyek_id', $editItem?->proyek_id ?? $selectedProyekId) === (string) $proyek->id)>{{ $proyek->nama_project }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="judul_tugas">Judul Tugas</label>
                                    <input type="text" class="form-control" id="judul_tugas" name="judul_tugas" value="{{ old('judul_tugas', $editItem?->judul_tugas) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="urutan">Urutan</label>
                                    <input type="number" class="form-control" id="urutan" name="urutan" min="0" max="999" value="{{ old('urutan', $editItem?->urutan) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="status_tugas_form">Status Tugas</label>
                                    <select class="form-select" id="status_tugas_form" name="status_tugas" required>
                                        @foreach ($statusTugasOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(old('status_tugas', $editItem?->status_tugas ?? 'belum_mulai') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="prioritas_tugas">Prioritas</label>
                                    <select class="form-select" id="prioritas_tugas" name="prioritas_tugas" required>
                                        @foreach ($prioritasTugasOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(old('prioritas_tugas', $editItem?->prioritas_tugas ?? 'sedang') === $key)>{{ $label }}</option>
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
                                    <label class="form-label" for="persentase_progres">Progres (%)</label>
                                    <input type="number" class="form-control" id="persentase_progres" name="persentase_progres" min="0" max="100" value="{{ old('persentase_progres', $editItem?->persentase_progres ?? 0) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $editItem?->tanggal_mulai?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="tanggal_target">Tanggal Target</label>
                                    <input type="date" class="form-control" id="tanggal_target" name="tanggal_target" value="{{ old('tanggal_target', $editItem?->tanggal_target?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="deskripsi_tugas">Deskripsi Tugas</label>
                                    <textarea class="form-control" id="deskripsi_tugas" name="deskripsi_tugas" rows="3">{{ old('deskripsi_tugas', $editItem?->deskripsi_tugas) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="catatan_tugas">Catatan Tugas</label>
                                    <textarea class="form-control" id="catatan_tugas" name="catatan_tugas" rows="3">{{ old('catatan_tugas', $editItem?->catatan_tugas) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">{{ $editItem ? 'Simpan Perubahan' : 'Simpan Tugas' }}</button>
                                @if ($editItem)
                                    <a href="{{ route('proyek.detail_tugas', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Board Tugas per Status</h3></div>
                <div class="card-body">
                    <div class="row row-cards">
                        @foreach ($statusTugasOptions as $statusKey => $statusLabel)
                            @php($kolomItems = $boardTugas[$statusKey] ?? collect())
                            <div class="col-md-6 col-xl-4 col-xxl">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <div>
                                            <h3 class="card-title">{{ $statusLabel }}</h3>
                                            <div class="text-secondary small">{{ $kolomItems->count() }} tugas</div>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column gap-3">
                                        @forelse ($kolomItems as $item)
                                            <div class="border rounded-3 p-3">
                                                <div class="d-flex justify-content-between gap-2 align-items-start">
                                                    <div>
                                                        <div class="fw-semibold">{{ $item->judul_tugas }}</div>
                                                        <div class="text-secondary small">{{ $item->proyek?->nama_project ?: '-' }}</div>
                                                    </div>
                                                    <span class="badge {{ $item->kelasBadgePrioritasTugas() }}">{{ $item->labelPrioritasTugas() }}</span>
                                                </div>

                                                <div class="mt-3 small text-secondary d-flex flex-column gap-1">
                                                    <span>PIC: {{ $item->penanggungJawab?->name ?: '-' }}</span>
                                                    <span>Target: {{ $item->tanggal_target?->format('d M Y') ?: '-' }}</span>
                                                </div>

                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between small mb-1">
                                                        <span>{{ $item->persentase_progres }}%</span>
                                                        @if ($item->tanggal_selesai)
                                                            <span class="text-secondary">Selesai {{ $item->tanggal_selesai->format('d M') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar {{ $item->persentase_progres >= 100 ? 'bg-green' : 'bg-primary' }}" style="width: {{ $item->persentase_progres }}%"></div>
                                                    </div>
                                                </div>

                                                @if ($isSuperadmin)
                                                    <form method="POST" action="{{ route('proyek.tugas.status_cepat', $item) }}" class="mt-3">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if ($selectedProyekId)
                                                            <input type="hidden" name="filter_proyek_id" value="{{ $selectedProyekId }}">
                                                        @endif
                                                        @if ($selectedStatusTugas)
                                                            <input type="hidden" name="filter_status_tugas" value="{{ $selectedStatusTugas }}">
                                                        @endif
                                                        @if ($selectedPenanggungJawab)
                                                            <input type="hidden" name="filter_penanggung_jawab" value="{{ $selectedPenanggungJawab }}">
                                                        @endif
                                                        <div class="row g-2">
                                                            <div class="col-7">
                                                                <select name="status_tugas" class="form-select form-select-sm">
                                                                    @foreach ($statusTugasOptions as $quickStatusKey => $quickStatusLabel)
                                                                        <option value="{{ $quickStatusKey }}" @selected($item->status_tugas === $quickStatusKey)>{{ $quickStatusLabel }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-5">
                                                                <input type="number" name="persentase_progres" class="form-control form-control-sm" min="0" max="100" value="{{ $item->persentase_progres }}">
                                                            </div>
                                                            <div class="col-12 d-flex justify-content-between gap-2">
                                                                <button type="submit" class="btn btn-outline-primary btn-sm">Perbarui Cepat</button>
                                                                <a href="{{ route('proyek.tugas.edit', ['tugasProyek' => $item] + $parameterFilter) }}" class="btn btn-ghost-secondary btn-sm">Form Lengkap</a>
                                                            </div>
                                                            <div class="col-12">
                                                                <a href="{{ route('proyek.tugas.histori', $item) }}" class="btn btn-ghost-secondary btn-sm w-100">Lihat Histori</a>
                                                            </div>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="mt-3">
                                                        <a href="{{ route('proyek.tugas.histori', $item) }}" class="btn btn-ghost-secondary btn-sm">Lihat Histori</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-secondary small">Belum ada tugas pada kolom ini.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Tugas Project</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Tugas</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Prioritas</th>
                                <th>Target</th>
                                <th>Progres</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->proyek?->nama_project ?: '-' }}</div>
                                        <div class="text-secondary small">{{ $item->proyek?->kode_project ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul_tugas }}</div>
                                        <div class="text-secondary small">{{ \Illuminate\Support\Str::limit($item->deskripsi_tugas ?: ($item->catatan_tugas ?: '-'), 80) }}</div>
                                    </td>
                                    <td>{{ $item->penanggungJawab?->name ?: '-' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusTugas() }}">{{ $item->labelStatusTugas() }}</span></td>
                                    <td><span class="badge {{ $item->kelasBadgePrioritasTugas() }}">{{ $item->labelPrioritasTugas() }}</span></td>
                                    <td>
                                        <div>{{ $item->tanggal_target?->format('d M Y') ?: '-' }}</div>
                                        <div class="text-secondary small">Mulai: {{ $item->tanggal_mulai?->format('d M Y') ?: '-' }}</div>
                                    </td>
                                    <td style="min-width: 12rem;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $item->persentase_progres }}%</span>
                                            @if ($item->tanggal_selesai)
                                                <span class="text-secondary">Selesai {{ $item->tanggal_selesai->format('d M') }}</span>
                                            @endif
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: {{ $item->persentase_progres }}%"></div>
                                        </div>
                                    </td>
                                    @if ($isSuperadmin)
                                        <td>
                                            <div class="btn-list justify-content-end flex-nowrap">
                                                <a href="{{ route('proyek.tugas.edit', ['tugasProyek' => $item] + $parameterFilter) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <a href="{{ route('proyek.tugas.histori', $item) }}" class="btn btn-outline-secondary btn-sm">Histori</a>
                                                <form method="POST" action="{{ route('proyek.tugas.destroy', $item) }}" onsubmit="return confirm('Hapus tugas ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    @if ($selectedProyekId)
                                                        <input type="hidden" name="filter_proyek_id" value="{{ $selectedProyekId }}">
                                                    @endif
                                                    @if ($selectedStatusTugas)
                                                        <input type="hidden" name="filter_status_tugas" value="{{ $selectedStatusTugas }}">
                                                    @endif
                                                    @if ($selectedPenanggungJawab)
                                                        <input type="hidden" name="filter_penanggung_jawab" value="{{ $selectedPenanggungJawab }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    @else
                                        <td>
                                            <a href="{{ route('proyek.tugas.histori', $item) }}" class="btn btn-outline-secondary btn-sm">Histori</a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary">Belum ada detail tugas project.</td>
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
