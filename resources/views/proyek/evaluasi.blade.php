@extends('tataletak.aplikasi')

@section('judul_halaman', 'Evaluasi')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Evaluasi')
@section('deskripsi_halaman', 'Pantau skor evaluasi, catatan tindak lanjut, dan project yang perlu perhatian lebih lanjut.')

@section('konten')
    @php
        $totalProject = $items->count();
        $projectTernilai = $items->filter(fn ($item) => $item->skor_evaluasi !== null)->count();
        $nilaiTinggi = $items->filter(fn ($item) => ($item->skor_evaluasi ?? 0) >= 80)->count();
        $perluPerhatian = $items->filter(fn ($item) => ($item->skor_evaluasi !== null && $item->skor_evaluasi < 70) || blank($item->catatan_evaluasi))->count();
        $rataRataSkor = $projectTernilai > 0 ? (int) round($items->whereNotNull('skor_evaluasi')->avg('skor_evaluasi')) : 0;
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Project</div><div class="h1 mb-0 mt-2">{{ number_format($totalProject, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Sudah Dinilai</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($projectTernilai, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Rata-rata Skor</div><div class="h1 mb-0 mt-2 text-green">{{ $rataRataSkor }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Perlu Perhatian</div><div class="h1 mb-0 mt-2 text-yellow">{{ number_format($perluPerhatian, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ringkasan Evaluasi Project</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Skor</th>
                                <th>Catatan Evaluasi</th>
                                <th>Diperbarui</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_project }}</div>
                                        <div class="text-secondary small">{{ $item->kode_project }}</div>
                                    </td>
                                    <td>{{ $item->penanggungJawab?->name ?: '-' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusProject() }}">{{ $item->labelStatusProject() }}</span></td>
                                    <td>
                                        @if ($item->skor_evaluasi !== null)
                                            <span class="badge {{ $item->skor_evaluasi >= 80 ? 'bg-green-lt text-green' : ($item->skor_evaluasi >= 70 ? 'bg-blue-lt text-blue' : 'bg-yellow-lt text-yellow') }}">
                                                {{ $item->skor_evaluasi }}/100
                                            </span>
                                        @else
                                            <span class="text-secondary">Belum dinilai</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary" style="min-width: 24rem;">
                                        {{ $item->catatan_evaluasi ?: 'Belum ada catatan evaluasi.' }}
                                    </td>
                                    <td>{{ $item->updated_at?->format('d M Y H:i') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">Belum ada data evaluasi project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
