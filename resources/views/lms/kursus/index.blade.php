@extends('tataletak.aplikasi')

@section('judul_halaman', 'Kursus')
@section('pratitel_halaman', 'LMS')
@section('judul_konten', 'Kursus')
@section('deskripsi_halaman', 'Kelola katalog kursus LMS dengan pola adaptasi dari project referensi kursusonline.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.playlist') }}" class="btn btn-outline-secondary">
            Playlist
        </a>

        @if (auth()->user()->adalahSuperadmin())
            <a href="#form-kursus" class="btn btn-primary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5l0 14" />
                    <path d="M5 12l14 0" />
                </svg>
                {{ $editItem ? 'Ubah Kursus' : 'Tambah Kursus' }}
            </a>
        @endif
    </div>
@endsection

@section('konten')
    @php
        $isSuperadmin = auth()->user()->adalahSuperadmin();
        $jumlahKursus = $items->count();
        $jumlahDenganThumbnail = $items->filter(fn ($item) => filled($item->thumbnail_url))->count();
        $totalMateri = $items->sum('materi_kursus_count');
        $totalKuis = $items->sum('kuis_lms_count');
        $parameterFilter = [];

        if ($kataKunci) {
            $parameterFilter['q'] = $kataKunci;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Kursus</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($jumlahKursus, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Dengan Thumbnail</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($jumlahDenganThumbnail, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Materi</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalMateri, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Kuis</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalKuis, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Kursus</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-8">
                            <label class="form-label" for="q">Cari Judul, Slug, atau Ringkasan</label>
                            <input
                                type="text"
                                class="form-control"
                                id="q"
                                name="q"
                                value="{{ $kataKunci }}"
                                placeholder="Contoh: laravel, desain, monitoring"
                            >
                        </div>
                        <div class="col-lg-4">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($kataKunci)
                                    <a href="{{ route('lms.kursus') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-kursus">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $editItem ? 'Ubah Kursus' : 'Tambah Kursus' }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('lms.kursus.update', $editItem) : route('lms.kursus.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            @if ($kataKunci)
                                <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label" for="judul">Judul Kursus</label>
                                    <input type="text" class="form-control" id="judul" name="judul"
                                        value="{{ old('judul', $editItem?->judul) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug"
                                        value="{{ old('slug', $editItem?->slug) }}"
                                        placeholder="Kosongkan untuk otomatis">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="ringkasan">Ringkasan</label>
                                    <textarea class="form-control" id="ringkasan" name="ringkasan" rows="3"
                                        placeholder="Ringkasan singkat kursus">{{ old('ringkasan', $editItem?->ringkasan) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="thumbnail_url">URL Thumbnail</label>
                                    <input type="url" class="form-control" id="thumbnail_url" name="thumbnail_url"
                                        value="{{ old('thumbnail_url', $editItem?->thumbnail_url) }}"
                                        placeholder="https://...">
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">
                                    {{ $editItem ? 'Simpan Perubahan' : 'Simpan Kursus' }}
                                </button>
                                @if ($editItem)
                                    <a href="{{ route('lms.kursus', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Kursus</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Judul</th>
                                <th>Slug</th>
                                <th>Ringkasan</th>
                                <th>Materi / Kuis</th>
                                @if ($isSuperadmin)
                                    <th class="w-1">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td class="w-1">
                                        @if ($item->thumbnail_url)
                                            <span class="avatar avatar-lg rounded" style="background-image: url('{{ $item->thumbnail_url }}')"></span>
                                        @else
                                            <span class="badge bg-secondary-lt">Belum ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul }}</div>
                                        <div class="text-secondary small">Diperbarui {{ $item->updated_at?->diffForHumans() }}</div>
                                    </td>
                                    <td><code>{{ $item->slug }}</code></td>
                                    <td class="text-wrap">
                                        {{ $item->ringkasan ? \Illuminate\Support\Str::limit($item->ringkasan, 120) : 'Belum ada ringkasan.' }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ number_format($item->materi_kursus_count, 0, ',', '.') }} materi</div>
                                        <a href="{{ route('lms.materi', ['kursus' => $item->id]) }}" class="text-secondary small">
                                            Lihat materi
                                        </a>
                                        <div class="small mt-1">
                                            <span class="fw-semibold">{{ number_format($item->kuis_lms_count, 0, ',', '.') }} kuis</span>
                                            ·
                                            <a href="{{ route('lms.kuis', ['target_tipe' => 'kursus', 'kursus' => $item->id]) }}" class="text-secondary">
                                                Lihat kuis
                                            </a>
                                        </div>
                                    </td>
                                    @if ($isSuperadmin)
                                        <td>
                                            <div class="btn-list justify-content-end flex-nowrap">
                                                <a href="{{ route('lms.kursus.edit', ['kursus' => $item] + $parameterFilter) }}"
                                                    class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <form method="POST" action="{{ route('lms.kursus.destroy', $item) }}"
                                                    onsubmit="return confirm('Hapus kursus ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperadmin ? 6 : 5 }}">
                                        <div class="empty py-5">
                                            <p class="empty-title">Belum ada kursus</p>
                                            <p class="empty-subtitle text-secondary mb-0">
                                                Tambahkan kursus pertama atau ubah filter pencarian untuk melihat hasil lain.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
