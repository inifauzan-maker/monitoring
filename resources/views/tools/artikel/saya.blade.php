@extends('tataletak.aplikasi')

@section('judul_halaman', 'Artikel Saya')
@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Artikel Saya')
@section('deskripsi_halaman', 'Kelola draft, artikel terbit, dan tindakan lanjutan untuk artikel yang Anda tulis.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.editorial') }}" class="btn btn-outline-secondary">Dashboard Editorial</a>
        <a href="{{ route('tools.artikel.kategori.index') }}" class="btn btn-outline-secondary">Kategori</a>
        <a href="{{ route('tools.artikel.create') }}" class="btn btn-primary">Buat Artikel</a>
    </div>
@endsection

@section('konten')
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Semua Artikel</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahSemua, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Draft</div><div class="h1 mb-0 mt-2 text-orange">{{ number_format($jumlahDraft, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Terjadwal</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($jumlahTerjadwal, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Diterbitkan</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($jumlahTerbit, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Siap Terbit</div><div class="h1 mb-0 mt-2 text-primary">{{ number_format($jumlahSiapTerbit, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Status</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua</option>
                                <option value="draft" @selected($statusDipilih === 'draft')>Draft</option>
                                <option value="terjadwal" @selected($statusDipilih === 'terjadwal')>Terjadwal</option>
                                <option value="diterbitkan" @selected($statusDipilih === 'diterbitkan')>Diterbitkan</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($statusDipilih)
                                    <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Kesiapan</th>
                                <th>Diperbarui</th>
                                <th>Revisi</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul }}</div>
                                        <div class="text-secondary small">{{ $item->kata_kunci_utama ?: 'Kata kunci belum diisi' }}</div>
                                    </td>
                                    <td>{{ $item->kategori?->nama ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $item->kelasBadgePublikasi() }}">{{ $item->labelStatusPublikasi() }}</span>
                                        <div class="text-secondary small mt-1">
                                            @if ($item->sedangTerjadwal())
                                                Terjadwal {{ $item->diterbitkan_pada?->format('d M Y H:i') }}
                                            @elseif ($item->sudahTerbitAktif())
                                                Terbit {{ $item->diterbitkan_pada?->format('d M Y H:i') }}
                                            @else
                                                Belum diterbitkan
                                            @endif
                                        </div>
                                    </td>
                                    @php($evaluasi = $item->evaluasiKesiapan())
                                    <td>
                                        <div class="fw-semibold">{{ $evaluasi['skor'] }}/100</div>
                                        <div class="text-secondary small">{{ $evaluasi['label'] }}</div>
                                    </td>
                                    <td>{{ $item->updated_at?->format('d M Y H:i') ?: '-' }}</td>
                                    <td>{{ number_format((int) $item->revisi_count, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="btn-list justify-content-end flex-nowrap">
                                            <a href="{{ route('tools.artikel.edit', $item) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                                            <a href="{{ route('tools.artikel.preview', $item) }}" class="btn btn-outline-secondary btn-sm">Preview</a>
                                            <a href="{{ route('tools.artikel.pdf', $item) }}" class="btn btn-outline-secondary btn-sm">PDF</a>
                                            @if ($item->adalahDraft())
                                                <form method="POST" action="{{ route('tools.artikel.terbitkan', $item) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                                        {{ $item->diterbitkan_pada?->isFuture() ? 'Jadwalkan' : 'Terbitkan' }}
                                                    </button>
                                                </form>
                                            @else
                                                @if ($item->sudahTerbitAktif())
                                                    <a href="{{ route('tools.artikel.show', $item) }}" class="btn btn-outline-success btn-sm">Lihat</a>
                                                @endif
                                                <form method="POST" action="{{ route('tools.artikel.batalkan_terbit', $item) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm">Unpublish</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('tools.artikel.destroy', $item) }}" onsubmit="return confirm('Hapus artikel ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary">Belum ada artikel untuk dikelola.</td>
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
