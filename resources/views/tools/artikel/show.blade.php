@extends('tataletak.aplikasi')

@php($adalahPreview = $adalahPreview ?? false)

@section('judul_halaman', $artikel->judul_seo ?: $artikel->judul)
@section('pratitel_halaman', 'Tools')
@section('judul_konten', $artikel->judul)
@section('deskripsi_halaman', $artikel->deskripsi_seo ?: $artikel->ringkasan)

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.editorial') }}" class="btn btn-outline-secondary">Dashboard Editorial</a>
        @if ((int) $artikel->penulis_id === (int) auth()->id())
            <a href="{{ route('tools.artikel.edit', $artikel) }}" class="btn btn-outline-primary">Ubah Artikel</a>
        @endif
        <a href="{{ route('tools.artikel.pdf', $artikel) }}" class="btn btn-outline-secondary">Export PDF</a>
        <a href="{{ route('tools.artikel') }}" class="btn btn-outline-secondary">Kembali ke Daftar</a>
    </div>
@endsection

@section('konten')
    <div class="row row-cards">
        @if ($adalahPreview)
            <div class="col-12">
                <div class="alert alert-warning mb-0">
                    Mode preview aktif. Halaman ini hanya bisa dilihat oleh penulis sebelum artikel diterbitkan.
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                @if ($artikel->url_gambar_unggulan)
                    <div class="card-img-top">
                        <img src="{{ $artikel->url_gambar_unggulan }}" alt="{{ $artikel->alt_gambar_unggulan ?: $artikel->judul }}"
                            class="w-100" style="max-height: 360px; object-fit: cover;">
                    </div>
                @endif

                <div class="card-body p-lg-5">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary-lt text-primary">{{ $artikel->kategori?->nama ?: 'Umum' }}</span>
                        <span class="badge bg-secondary-lt text-secondary">{{ $artikel->labelTingkatKeahlian() }}</span>
                        <span class="badge {{ $artikel->kelasBadgePublikasi() }}">{{ $artikel->labelStatusPublikasi() }}</span>
                    </div>

                    <h1 class="mb-3">{{ $artikel->judul }}</h1>

                    <div class="text-secondary mb-4">
                        <div>{{ $artikel->penulis?->name ?: 'Penulis belum diatur' }}</div>
                        <div>
                            @if ($artikel->sedangTerjadwal())
                                Dijadwalkan {{ $artikel->diterbitkan_pada->format('d M Y H:i') }}
                            @elseif ($artikel->diterbitkan_pada)
                                {{ $artikel->diterbitkan_pada->format('d M Y H:i') }}
                            @elseif ($adalahPreview)
                                Belum diterbitkan
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="fs-5 text-secondary mb-4">{{ $artikel->ringkasan }}</div>

                    <div class="markdown">
                        {!! $artikel->konten !!}
                    </div>
                </div>
            </div>
        </div>

        @if ($artikel->bio_penulis || filled($artikel->sumber_referensi))
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Pendukung</h3>
                    </div>
                    <div class="card-body">
                        @if ($artikel->bio_penulis)
                            <div class="mb-4">
                                <div class="fw-semibold mb-2">Bio Penulis</div>
                                <div class="text-secondary">{{ $artikel->bio_penulis }}</div>
                            </div>
                        @endif

                        @if (filled($artikel->sumber_referensi))
                            <div>
                                <div class="fw-semibold mb-2">Sumber Referensi</div>
                                <div class="list-group list-group-flush">
                                    @foreach ($artikel->sumber_referensi as $sumber)
                                        <a href="{{ $sumber }}" target="_blank" rel="noopener noreferrer"
                                            class="list-group-item list-group-item-action">
                                            {{ $sumber }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
