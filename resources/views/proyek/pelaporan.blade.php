@extends('tataletak.aplikasi')

@section('judul_halaman', 'Pelaporan')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Pelaporan')
@section('deskripsi_halaman', 'Sistem pelaporan project untuk melihat distribusi status, progres rata-rata, dan aktivitas tugas terbaru.')

@section('konten')
    @php
        $totalProject = $semuaProject->count();
        $totalTugas = $semuaProject->sum('tugas_count');
        $projectSelesai = $semuaProject->where('status_project', 'selesai')->count();
        $tugasSelesai = $semuaProject->sum('tugas_selesai_count');
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Project</div><div class="h1 mb-0 mt-2">{{ number_format($totalProject, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Tugas</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($totalTugas, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Project Selesai</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($projectSelesai, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Tugas Selesai</div><div class="h1 mb-0 mt-2">{{ number_format($tugasSelesai, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Status Project</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ringkasanStatusProject as $item)
                                <tr>
                                    <td>{{ $item['label'] }}</td>
                                    <td class="fw-semibold">{{ number_format($item['total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-secondary">Belum ada ringkasan status project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Status Tugas</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ringkasanStatusTugas as $item)
                                <tr>
                                    <td>{{ $item['label'] }}</td>
                                    <td class="fw-semibold">{{ number_format($item['total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-secondary">Belum ada ringkasan status tugas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Laporan Ringkas Project</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Prioritas</th>
                                <th>Tugas</th>
                                <th>Rata-rata Progres</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($semuaProject as $item)
                                @php($progres = $item->persentaseProgres())
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_project }}</div>
                                        <div class="text-secondary small">{{ $item->kode_project }}</div>
                                    </td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusProject() }}">{{ $item->labelStatusProject() }}</span></td>
                                    <td><span class="badge {{ $item->kelasBadgePrioritasProject() }}">{{ $item->labelPrioritasProject() }}</span></td>
                                    <td>{{ $item->totalTugasSelesai() }}/{{ $item->totalTugas() }}</td>
                                    <td style="min-width: 14rem;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $progres }}%</span>
                                            <span class="text-secondary">{{ number_format($item->rata_rata_progres ?? 0, 0, ',', '.') }} rata-rata</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar {{ $progres >= 100 ? 'bg-green' : 'bg-primary' }}" style="width: {{ $progres }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">Belum ada data pelaporan project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Aktivitas Tugas Terbaru</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Tugas</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Progres</th>
                                <th>Diperbarui</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tugasTerbaru as $item)
                                <tr>
                                    <td>{{ $item->proyek?->nama_project ?: '-' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul_tugas }}</div>
                                        <div class="text-secondary small">Prioritas: {{ $item->labelPrioritasTugas() }}</div>
                                    </td>
                                    <td>{{ $item->penanggungJawab?->name ?: '-' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusTugas() }}">{{ $item->labelStatusTugas() }}</span></td>
                                    <td>{{ $item->persentase_progres }}%</td>
                                    <td>{{ $item->updated_at?->format('d M Y H:i') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">Belum ada aktivitas tugas terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
