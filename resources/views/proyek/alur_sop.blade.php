@extends('tataletak.aplikasi')

@section('judul_halaman', 'Alur & SOP')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Alur & SOP')
@section('deskripsi_halaman', 'Dokumentasikan alur kerja project, SOP ringkas, dan PIC utama agar eksekusi tim tetap seragam.')

@section('konten')
    @php
        $totalProject = $items->count();
        $projectDenganAlur = $items->filter(fn ($item) => filled($item->alur_kerja))->count();
        $projectDenganSop = $items->filter(fn ($item) => filled($item->sop_ringkas))->count();
        $projectButuhPembaharuan = $items->filter(fn ($item) => blank($item->alur_kerja) || blank($item->sop_ringkas))->count();
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Project</div><div class="h1 mb-0 mt-2">{{ number_format($totalProject, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Sudah Punya Alur</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($projectDenganAlur, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Sudah Punya SOP</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($projectDenganSop, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Perlu Dilengkapi</div><div class="h1 mb-0 mt-2 text-yellow">{{ number_format($projectButuhPembaharuan, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Dokumen Alur Kerja & SOP</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Alur Kerja</th>
                                <th>SOP Ringkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_project }}</div>
                                        <div class="text-secondary small">{{ $item->kode_project }}{{ $item->klien ? ' • '.$item->klien : '' }}</div>
                                    </td>
                                    <td>{{ $item->penanggungJawab?->name ?: '-' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusProject() }}">{{ $item->labelStatusProject() }}</span></td>
                                    <td class="text-secondary" style="min-width: 20rem;">
                                        @if (filled($item->alur_kerja))
                                            {!! nl2br(e($item->alur_kerja)) !!}
                                        @else
                                            <span class="text-warning">Alur kerja belum diisi.</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary" style="min-width: 20rem;">
                                        @if (filled($item->sop_ringkas))
                                            {!! nl2br(e($item->sop_ringkas)) !!}
                                        @else
                                            <span class="text-warning">SOP ringkas belum diisi.</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">Belum ada project untuk didokumentasikan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
