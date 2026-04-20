@extends('tataletak.aplikasi')

@section('judul_halaman', 'Penanggung Jawab')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Penanggung Jawab')
@section('deskripsi_halaman', 'Lihat siapa yang menangani project dan tugas, seberapa padat bebannya, serta progres kerja per PIC.')

@section('konten')
    @php
        $totalPicAktif = $items->count();
        $totalProject = $items->sum('total_project');
        $totalTugas = $items->sum('total_tugas');
        $rataRataProgres = $items->count() > 0 ? (int) round($items->avg('rata_rata_progres')) : 0;
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">PIC Aktif</div><div class="h1 mb-0 mt-2">{{ number_format($totalPicAktif, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Project Ditangani</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($totalProject, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Tugas</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($totalTugas, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Rata-rata Progres</div><div class="h1 mb-0 mt-2">{{ $rataRataProgres }}%</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Distribusi Tanggung Jawab</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>PIC</th>
                                <th>Level Akses</th>
                                <th>Project</th>
                                <th>Tugas</th>
                                <th>Selesai</th>
                                <th>Tertunda</th>
                                <th>Rata-rata Progres</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item['user']->name }}</div>
                                        <div class="text-secondary small">{{ $item['user']->email }}</div>
                                    </td>
                                    <td><span class="badge {{ $item['user']->kelasBadgeLevelAkses() }}">{{ $item['user']->labelLevelAkses() }}</span></td>
                                    <td>{{ number_format($item['total_project'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($item['total_tugas'], 0, ',', '.') }}</td>
                                    <td class="text-green fw-semibold">{{ number_format($item['tugas_selesai'], 0, ',', '.') }}</td>
                                    <td class="text-yellow fw-semibold">{{ number_format($item['tugas_tertunda'], 0, ',', '.') }}</td>
                                    <td style="min-width: 14rem;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $item['rata_rata_progres'] }}%</span>
                                            <span class="text-secondary">{{ $item['tugas_selesai'] }}/{{ $item['total_tugas'] ?: 0 }} selesai</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: {{ $item['rata_rata_progres'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary">Belum ada PIC yang memiliki project atau tugas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
