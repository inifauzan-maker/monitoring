@extends('tataletak.aplikasi')

@section('judul_halaman', 'Histori Progres Tugas')
@section('pratitel_halaman', 'Projects')
@section('judul_konten', 'Histori Progres Tugas')
@section('deskripsi_halaman', 'Lacak perubahan status dan progres tiap tugas, termasuk siapa yang memperbaruinya dan kapan perubahan terjadi.')

@section('aksi_halaman')
    <a href="{{ route('proyek.detail_tugas', ['proyek_id' => $tugas->proyek_id]) }}" class="btn btn-outline-secondary">
        Kembali ke Detail Tugas
    </a>
@endsection

@section('konten')
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Tugas</div><div class="fw-semibold mt-2">{{ $tugas->judul_tugas }}</div><div class="text-secondary small">{{ $tugas->proyek?->nama_project ?: '-' }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Status Saat Ini</div><div class="mt-2"><span class="badge {{ $tugas->kelasBadgeStatusTugas() }}">{{ $tugas->labelStatusTugas() }}</span></div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Progres Saat Ini</div><div class="h1 mb-0 mt-2">{{ $tugas->persentase_progres }}%</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Histori</div><div class="h1 mb-0 mt-2">{{ number_format($histori->total(), 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Timeline Perubahan</h3></div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($histori as $item)
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $item->catatan_histori ?: 'Perubahan progres tugas.' }}</div>
                                        <div class="text-secondary small">
                                            {{ $item->pencatat?->name ?: 'Sistem' }} • {{ $item->created_at?->format('d M Y H:i') ?: '-' }}
                                        </div>
                                    </div>
                                    <span class="badge bg-secondary-lt text-secondary">{{ $item->created_at?->diffForHumans() }}</span>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <div class="text-secondary small text-uppercase fw-semibold">Status</div>
                                        <div class="mt-1">
                                            <span class="text-secondary">{{ $item->labelStatusSebelum() }}</span>
                                            <span class="mx-1">→</span>
                                            <span class="fw-semibold">{{ $item->labelStatusSesudah() }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-secondary small text-uppercase fw-semibold">Progres</div>
                                        <div class="mt-1">
                                            <span class="text-secondary">{{ $item->progres_sebelum ?? 0 }}%</span>
                                            <span class="mx-1">→</span>
                                            <span class="fw-semibold">{{ $item->progres_sesudah }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary">Belum ada histori progres untuk tugas ini.</div>
                        @endforelse
                    </div>
                </div>
                @if ($histori->hasPages())
                    <div class="card-footer">{{ $histori->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
