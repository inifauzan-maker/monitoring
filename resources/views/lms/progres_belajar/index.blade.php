@extends('tataletak.aplikasi')

@section('judul_halaman', 'Progres Belajar')
@section('pratitel_halaman', 'LMS')
@section('judul_konten', 'Progres Belajar')
@section('deskripsi_halaman', 'Pantau progres belajar per materi dan per kursus dengan pola adaptasi dari lesson progress kursusonline.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.kursus') }}" class="btn btn-outline-secondary">
            Kursus
        </a>
        <a href="{{ route('lms.materi') }}" class="btn btn-outline-secondary">
            Materi
        </a>
        <a href="#form-progres-belajar" class="btn btn-primary">
            Perbarui Progres
        </a>
    </div>
@endsection

@section('konten')
    @php
        $parameterFilter = [];

        if ($isSuperadmin) {
            $parameterFilter['pengguna'] = $penggunaTarget->id;
        }

        if ($kursusDipilih) {
            $parameterFilter['kursus'] = $kursusDipilih->id;
        }

        if ($statusDipilih) {
            $parameterFilter['status'] = $statusDipilih;
        }

        if ($kataKunci) {
            $parameterFilter['q'] = $kataKunci;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Kursus</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($ringkasanUtama['jumlah_kursus'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Materi</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($ringkasanUtama['jumlah_materi'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Materi Selesai</div>
                    <div class="h1 mb-0 mt-2 text-green">{{ number_format($ringkasanUtama['jumlah_selesai'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Rata Progres</div>
                    <div class="h1 mb-0 mt-2">{{ $ringkasanUtama['rata_progres'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="d-flex">
                    <div>
                        <svg class="icon alert-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 9h.01" />
                            <path d="M11 12h1v4h1" />
                            <path d="M12 3a9 9 0 1 0 9 9" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="alert-title mb-1">Target Monitoring</h4>
                        <div>
                            {{ $penggunaTarget->name }} <span class="text-secondary">({{ $penggunaTarget->email }})</span>
                            @if ($isSuperadmin && auth()->id() !== $penggunaTarget->id)
                                <span class="badge bg-blue-lt text-blue ms-2">Mode Monitoring Pengguna</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Progres Belajar</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        @if ($isSuperadmin)
                            <div class="col-lg-3">
                                <label class="form-label" for="pengguna">Pengguna</label>
                                <select class="form-select" id="pengguna" name="pengguna">
                                    @foreach ($opsiPengguna as $pengguna)
                                        <option value="{{ $pengguna->id }}" @selected($penggunaTarget->id === $pengguna->id)>
                                            {{ $pengguna->name }} - {{ $pengguna->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-lg-3">
                            <label class="form-label" for="kursus">Kursus</label>
                            <select class="form-select" id="kursus" name="kursus">
                                <option value="">Semua Kursus</option>
                                @foreach ($opsiKursus as $kursus)
                                    <option value="{{ $kursus->id }}" @selected($kursusDipilih?->id === $kursus->id)>{{ $kursus->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua Status</option>
                                @foreach ($opsiStatus as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($statusDipilih === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label" for="q">Cari Materi, YouTube ID, atau Kursus</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $kataKunci }}"
                                placeholder="Contoh: pengantar, laravel, video001">
                        </div>
                        <div class="col-lg-12">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($parameterFilter !== [] || $kataKunci)
                                    <a href="{{ route('lms.progres_belajar') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12" id="form-progres-belajar">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Perbarui Progres Belajar</h3>
                </div>
                <div class="card-body">
                    @php
                        $progresForm = $materiDipilih?->progresBelajar?->first();
                    @endphp

                    <form method="POST" action="{{ route('lms.progres_belajar.store') }}" class="row g-3">
                        @csrf

                        @if ($isSuperadmin)
                            <input type="hidden" name="pengguna_id" value="{{ $penggunaTarget->id }}">
                            <input type="hidden" name="filter_pengguna" value="{{ $penggunaTarget->id }}">
                        @endif
                        <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                        <input type="hidden" name="filter_status" value="{{ $statusDipilih }}">
                        <input type="hidden" name="filter_q" value="{{ $kataKunci }}">

                        <div class="col-lg-6">
                            <label class="form-label" for="materi_kursus_id">Materi</label>
                            <select class="form-select" id="materi_kursus_id" name="materi_kursus_id" required>
                                <option value="">Pilih Materi</option>
                                @foreach ($opsiMateri as $materi)
                                    <option value="{{ $materi->id }}"
                                        @selected((int) old('materi_kursus_id', $materiDipilih?->id) === $materi->id)>
                                        {{ $materi->kursus?->judul }} - {{ $materi->urutan }}. {{ $materi->judul }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="detik_terakhir">Detik Terakhir</label>
                            <input type="number" class="form-control" id="detik_terakhir" name="detik_terakhir"
                                value="{{ old('detik_terakhir', $progresForm?->detik_terakhir ?? 0) }}" min="0">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="persen_progres">Persen Progres</label>
                            <input type="number" class="form-control" id="persen_progres" name="persen_progres"
                                value="{{ old('persen_progres', $progresForm?->persen_progres ?? 0) }}" min="0" max="100">
                        </div>
                        <div class="col-12">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="tandai_selesai" value="1"
                                    @checked(old('tandai_selesai', $progresForm?->sudahSelesai()))>
                                <span class="form-check-label">Tandai materi ini sudah selesai</span>
                            </label>
                        </div>
                        @if ($materiDipilih)
                            <div class="col-12">
                                <div class="text-secondary small">
                                    Materi terpilih:
                                    <span class="fw-semibold">{{ $materiDipilih->kursus?->judul }}</span>
                                    - {{ $materiDipilih->judul }}
                                    @if ($progresForm)
                                        , status saat ini
                                        <span class="badge {{ $progresForm->kelasBadgeStatus() }}">{{ $progresForm->labelStatus() }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Simpan Progres</button>
                                @if ($materiDipilih)
                                    <a href="{{ route('lms.progres_belajar', $parameterFilter) }}" class="btn btn-outline-secondary">Batal Pilih Materi</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan per Kursus</h3>
                </div>
                <div class="card-body">
                    <div class="row row-cards">
                        @forelse ($ringkasanKursus as $kursus)
                            <div class="col-md-6 col-xl-4">
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $kursus->judul }}</div>
                                                <div class="text-secondary small">
                                                    {{ number_format($kursus->materi_selesai_lms, 0, ',', '.') }} /
                                                    {{ number_format($kursus->total_materi_lms, 0, ',', '.') }} materi selesai
                                                </div>
                                            </div>
                                            <span class="badge bg-blue-lt text-blue">{{ $kursus->persen_selesai_lms }}%</span>
                                        </div>
                                        <div class="progress progress-sm mt-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $kursus->persen_selesai_lms }}%"
                                                role="progressbar" aria-valuenow="{{ $kursus->persen_selesai_lms }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="row mt-3 text-secondary small">
                                            <div class="col-6">Sedang berjalan: {{ $kursus->materi_berjalan_lms }}</div>
                                            <div class="col-6 text-end">Rata progres: {{ $kursus->rata_progres_lms }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty py-4">
                                    <p class="empty-title">Belum ada ringkasan kursus</p>
                                    <p class="empty-subtitle text-secondary mb-0">
                                        Tambahkan materi atau mulai perbarui progres belajar untuk melihat ringkasannya.
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Progres per Materi</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kursus</th>
                                <th>Materi</th>
                                <th>Status</th>
                                <th>Progres</th>
                                <th>Detik Terakhir</th>
                                <th>Selesai Pada</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $progres = $item->progresBelajar->first();
                                    $statusLabel = $progres?->labelStatus() ?? 'Belum Mulai';
                                    $statusKelas = $progres?->kelasBadgeStatus() ?? 'bg-secondary-lt text-secondary';
                                    $persen = $progres?->persen_progres ?? 0;
                                @endphp
                                <tr data-materi-row="{{ $item->id }}">
                                    <td>
                                        <div class="fw-semibold">{{ $item->kursus?->judul }}</div>
                                        <div class="text-secondary small">{{ $item->kursus?->slug }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->urutan }}. {{ $item->judul }}</div>
                                        <div class="text-secondary small"><code>{{ $item->youtube_id }}</code></div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusKelas }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td style="min-width: 180px;">
                                        <div class="d-flex justify-content-between small text-secondary mb-1">
                                            <span>{{ $persen }}%</span>
                                            <span>{{ $progres?->sudahSelesai() ? 'Tuntas' : 'Aktif' }}</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: {{ $persen }}%"
                                                role="progressbar" aria-valuenow="{{ $persen }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($progres?->detik_terakhir ?? 0, 0, ',', '.') }} detik</td>
                                    <td>{{ $progres?->selesai_pada?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="btn-list justify-content-end flex-nowrap">
                                            <a href="{{ route('lms.progres_belajar', $parameterFilter + ['materi' => $item->id]) }}"
                                                class="btn btn-outline-primary btn-sm">Perbarui</a>
                                            @if (! $progres?->sudahSelesai())
                                                <form method="POST" action="{{ route('lms.progres_belajar.store') }}">
                                                    @csrf
                                                    @if ($isSuperadmin)
                                                        <input type="hidden" name="pengguna_id" value="{{ $penggunaTarget->id }}">
                                                        <input type="hidden" name="filter_pengguna" value="{{ $penggunaTarget->id }}">
                                                    @endif
                                                    <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                                                    <input type="hidden" name="filter_status" value="{{ $statusDipilih }}">
                                                    <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                                                    <input type="hidden" name="materi_kursus_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="persen_progres" value="100">
                                                    <input type="hidden" name="tandai_selesai" value="1">
                                                    <button type="submit" class="btn btn-outline-success btn-sm">Selesai</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty py-5">
                                            <p class="empty-title">Belum ada progres yang bisa ditampilkan</p>
                                            <p class="empty-subtitle text-secondary mb-0">
                                                Ubah filter atau simpan progres belajar untuk mulai memantau materi.
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
