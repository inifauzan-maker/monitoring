@extends('tataletak.aplikasi')

@section('judul_halaman', 'Artikel')
@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Artikel')
@section('deskripsi_halaman', 'Daftar artikel terbit dan ringkasan konten yang dikelola dari dalam aplikasi.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.editorial') }}" class="btn btn-outline-secondary">Dashboard Editorial</a>
        <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-secondary">Artikel Saya</a>
        <a href="{{ route('tools.artikel.kategori.index') }}" class="btn btn-outline-secondary">Kategori</a>
        <a href="{{ route('tools.artikel.create') }}" class="btn btn-primary">Buat Artikel</a>
    </div>
@endsection

@section('konten')
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Artikel Terbit</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($jumlahArtikel, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Kategori Aktif</div>
                    <div class="h1 mb-0 mt-2 text-primary">{{ number_format($jumlahKategoriAktif, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Siap Terbit</div>
                    <div class="h1 mb-0 mt-2 text-green">{{ number_format($jumlahSiapTerbit, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Terbit Terbaru</div>
                    <div class="h3 mb-0 mt-2">{{ $tanggalTerbitTerbaru?->format('d M Y') ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Artikel</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label" for="q">Cari</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $kataKunci }}"
                                placeholder="Judul, ringkasan, atau kata kunci utama">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="kategori">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategori as $itemKategori)
                                    <option value="{{ $itemKategori->slug }}" @selected($kategoriDipilih?->id === $itemKategori->id)>
                                        {{ $itemKategori->nama }} ({{ number_format((int) $itemKategori->jumlah_artikel_diterbitkan, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($kataKunci !== '' || $kategoriDipilih)
                                    <a href="{{ route('tools.artikel') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Artikel Saya Terbaru</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($artikelSayaTerbaru as $itemSaya)
                        <a href="{{ route('tools.artikel.edit', $itemSaya) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $itemSaya->judul }}</div>
                                    <div class="text-secondary small">
                                        {{ $itemSaya->kategori?->nama ?: 'Tanpa kategori' }} • Skor {{ $itemSaya->evaluasiKesiapan()['skor'] }}
                                    </div>
                                </div>
                                <span class="badge {{ $itemSaya->kelasBadgePublikasi() }}">{{ $itemSaya->labelStatusPublikasi() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="card-body">
                            <div class="empty py-5">
                                <p class="empty-title">Belum ada artikel pribadi</p>
                                <p class="empty-subtitle text-secondary">Mulai dari draft pertama Anda.</p>
                                <div class="empty-action">
                                    <a href="{{ route('tools.artikel.create') }}" class="btn btn-primary">Buat Artikel</a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
                @if ($artikelSayaTerbaru->isNotEmpty())
                    <div class="card-footer">
                        <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-primary">Lihat Semua</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row row-cards">
                @forelse ($items as $item)
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <span class="badge bg-primary-lt text-primary">{{ $item->kategori?->nama ?: 'Umum' }}</span>
                                    <span class="badge bg-secondary-lt text-secondary">{{ $item->labelTingkatKeahlian() }}</span>
                                </div>

                                <h3 class="card-title mb-2">
                                    <a href="{{ route('tools.artikel.show', $item) }}" class="text-reset text-decoration-none">
                                        {{ $item->judul }}
                                    </a>
                                </h3>

                                <p class="text-secondary mb-4">{{ \Illuminate\Support\Str::limit($item->ringkasan, 160) }}</p>

                                <div class="mt-auto">
                                    <div class="text-secondary small mb-3">
                                        <div>{{ $item->penulis?->name ?: 'Penulis belum diatur' }}</div>
                                        <div>{{ $item->diterbitkan_pada?->format('d M Y H:i') ?: '-' }}</div>
                                    </div>
                                    <a href="{{ route('tools.artikel.show', $item) }}" class="btn btn-outline-primary">Buka Artikel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="empty py-6">
                                    <p class="empty-title">Belum ada artikel terbit</p>
                                    <p class="empty-subtitle text-secondary">Mulai buat draft, lengkapi metadata, lalu terbitkan dari menu artikel.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if ($items->count() > 0)
            <div class="col-12">
                <div class="card">
                    <div class="card-footer">{{ $items->links() }}</div>
                </div>
            </div>
        @endif
    </div>
@endsection
