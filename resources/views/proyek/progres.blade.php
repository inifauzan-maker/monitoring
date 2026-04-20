@extends('tataletak.aplikasi')

@section('judul_halaman', 'Progres')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Progres')
@section('deskripsi_halaman', 'Pantau progres tiap project, jumlah tugas selesai, dan potensi keterlambatan dalam satu dashboard ringkas.')

@section('konten')
    @php
        $totalProject = $items->count();
        $projectSelesai = $items->where('status_project', 'selesai')->count();
        $rataRataProgres = $items->count() > 0 ? (int) round($items->avg(fn ($item) => $item->persentaseProgres())) : 0;
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Project Dipantau</div><div class="h1 mb-0 mt-2">{{ number_format($totalProject, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Rata-rata Progres</div><div class="h1 mb-0 mt-2">{{ $rataRataProgres }}%</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Project Selesai</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($projectSelesai, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Tugas Terlambat</div><div class="h1 mb-0 mt-2 text-red">{{ number_format($tugasTerlambat, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Progres</h3></div>
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
                        <div class="col-md-8">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($selectedStatusProject)
                                    <a href="{{ route('proyek.progres') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Progres per Project</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Tugas</th>
                                <th>Progres</th>
                                <th>Target Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php($progres = $item->persentaseProgres())
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_project }}</div>
                                        <div class="text-secondary small">{{ $item->kode_project }}</div>
                                    </td>
                                    <td>{{ $item->penanggungJawab?->name ?: '-' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusProject() }}">{{ $item->labelStatusProject() }}</span></td>
                                    <td>{{ $item->totalTugasSelesai() }}/{{ $item->totalTugas() }}</td>
                                    <td style="min-width: 14rem;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $progres }}%</span>
                                            <a href="{{ route('proyek.detail_tugas', ['proyek_id' => $item->id]) }}" class="text-decoration-none">Lihat tugas</a>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar {{ $progres >= 100 ? 'bg-green' : 'bg-primary' }}" style="width: {{ $progres }}%"></div>
                                        </div>
                                    </td>
                                    <td>{{ $item->tanggal_target_selesai?->format('d M Y') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">Belum ada data progres project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
