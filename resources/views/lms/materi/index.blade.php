@extends('tataletak.aplikasi')

@section('judul_halaman', 'Materi')
@section('pratitel_halaman', 'LMS')
@section('judul_konten', 'Materi')
@section('deskripsi_halaman', 'Kelola lesson atau video materi per kursus dengan adaptasi dari modul referensi kursusonline.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.kursus') }}" class="btn btn-outline-secondary">
            Kursus
        </a>

        @if (auth()->user()->adalahSuperadmin())
            <a href="#form-materi" class="btn btn-primary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5l0 14" />
                    <path d="M5 12l14 0" />
                </svg>
                {{ $editItem ? 'Ubah Materi' : 'Tambah Materi' }}
            </a>
        @endif
    </div>
@endsection

@section('konten')
    @php
        $isSuperadmin = auth()->user()->adalahSuperadmin();
        $jumlahMateri = $items->count();
        $jumlahKursusAktif = $items->pluck('kursus_id')->unique()->count();
        $totalDurasiDetik = $items->sum('durasi_detik');
        $jam = intdiv($totalDurasiDetik, 3600);
        $menit = intdiv($totalDurasiDetik % 3600, 60);
        $labelDurasi = $jam > 0 ? sprintf('%dj %dm', $jam, $menit) : sprintf('%dm', $menit);
        $parameterFilter = [];

        if ($kursusDipilih) {
            $parameterFilter['kursus'] = $kursusDipilih->id;
        }

        if ($kataKunci) {
            $parameterFilter['q'] = $kataKunci;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Materi</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($jumlahMateri, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Kursus Aktif</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($jumlahKursusAktif, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Durasi</div>
                    <div class="h3 mb-0 mt-2">{{ $labelDurasi }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Filter Kursus</div>
                    <div class="h3 mb-0 mt-2">{{ $kursusDipilih?->judul ?? 'Semua Kursus' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Materi</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label" for="kursus">Kursus</label>
                            <select class="form-select" id="kursus" name="kursus">
                                <option value="">Semua Kursus</option>
                                @foreach ($opsiKursus as $kursus)
                                    <option value="{{ $kursus->id }}" @selected($kursusDipilih?->id === $kursus->id)>{{ $kursus->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label" for="q">Cari Judul, YouTube ID, atau Kursus</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $kataKunci }}"
                                placeholder="Contoh: pengantar, video123, laravel">
                        </div>
                        <div class="col-lg-3">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($kursusDipilih || $kataKunci)
                                    <a href="{{ route('lms.materi') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-materi">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $editItem ? 'Ubah Materi' : 'Tambah Materi' }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('lms.materi.update', $editItem) : route('lms.materi.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            @if ($kursusDipilih)
                                <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih->id }}">
                            @endif
                            @if ($kataKunci)
                                <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="kursus_id">Kursus</label>
                                    <select class="form-select" id="kursus_id" name="kursus_id" required>
                                        <option value="">Pilih Kursus</option>
                                        @foreach ($opsiKursus as $kursus)
                                            <option value="{{ $kursus->id }}"
                                                @selected((int) old('kursus_id', $editItem?->kursus_id ?? $kursusDipilih?->id) === $kursus->id)>
                                                {{ $kursus->judul }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="judul">Judul Materi</label>
                                    <input type="text" class="form-control" id="judul" name="judul"
                                        value="{{ old('judul', $editItem?->judul) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="youtube_id">YouTube ID</label>
                                    <input type="text" class="form-control" id="youtube_id" name="youtube_id"
                                        value="{{ old('youtube_id', $editItem?->youtube_id) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="durasi_detik">Durasi Detik</label>
                                    <input type="number" class="form-control" id="durasi_detik" name="durasi_detik"
                                        value="{{ old('durasi_detik', $editItem?->durasi_detik ?? 0) }}" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="urutan">Urutan</label>
                                    <input type="number" class="form-control" id="urutan" name="urutan"
                                        value="{{ old('urutan', $editItem?->urutan) }}" min="1"
                                        placeholder="Otomatis jika kosong">
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">
                                    {{ $editItem ? 'Simpan Perubahan' : 'Simpan Materi' }}
                                </button>
                                @if ($editItem)
                                    <a href="{{ route('lms.materi', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
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
                    <h3 class="card-title">Daftar Materi</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kursus</th>
                                <th>Judul Materi</th>
                                <th>YouTube ID</th>
                                <th>Durasi</th>
                                <th>Urutan</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->kursus?->judul }}</div>
                                        <div class="text-secondary small">{{ $item->kursus?->slug }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul }}</div>
                                        <div class="text-secondary small">
                                            <a href="{{ route('lms.materi.show', $item) }}">Buka halaman materi</a>
                                        </div>
                                    </td>
                                    <td><code>{{ $item->youtube_id }}</code></td>
                                    <td>{{ $item->labelDurasi() }}</td>
                                    <td>{{ $item->urutan }}</td>
                                    <td>
                                        <div class="btn-list justify-content-end flex-nowrap">
                                            <a href="{{ route('lms.materi.show', $item) }}"
                                                class="btn btn-primary btn-sm">Buka</a>
                                            @if ($isSuperadmin)
                                                <a href="{{ route('lms.materi.edit', ['materiKursus' => $item] + $parameterFilter) }}"
                                                    class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <form method="POST" action="{{ route('lms.materi.destroy', $item) }}"
                                                    onsubmit="return confirm('Hapus materi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                                                    <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty py-5">
                                            <p class="empty-title">Belum ada materi</p>
                                            <p class="empty-subtitle text-secondary mb-0">
                                                Tambahkan materi pertama atau ubah filter untuk melihat data lain.
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
