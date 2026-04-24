@extends('tataletak.aplikasi')

@section('judul_halaman', 'Beranda')
@section('pratitel_halaman', $pratitelBeranda)
@section('judul_konten', 'Beranda')
@section('deskripsi_halaman', $deskripsiBeranda)

@section('aksi_halaman')
    <div class="btn-list">
        <span class="badge {{ auth()->user()->kelasBadgeLevelAkses() }} px-3 py-2">
            {{ auth()->user()->labelLevelAkses() }}
        </span>

        <a href="{{ route('notifikasi.index') }}" class="btn btn-outline-secondary">
            @include('komponen.ikon_menu', ['ikon' => 'alarm'])
            <span>Notifikasi</span>
        </a>

        @if (auth()->user()->adalahSuperadmin())
            <a href="{{ route('administrasi.log_aktivitas.index') }}" class="btn btn-primary">
                @include('komponen.ikon_menu', ['ikon' => 'log_aktivitas'])
                <span>Log Aktivitas</span>
            </a>
        @else
            <a href="{{ route('proyek.detail_tugas') }}" class="btn btn-primary">
                @include('komponen.ikon_menu', ['ikon' => 'detail_tugas'])
                <span>Detail Tugas</span>
            </a>
        @endif
    </div>
@endsection

@section('konten')
    @php($pengguna = auth()->user())

    <div class="row row-deck row-cards">
        @foreach ($kartuRingkasan as $kartu)
            <div class="col-sm-6 col-lg-3">
                <div class="card {{ $kartu['kelas_kartu'] }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="text-secondary text-uppercase fw-semibold small">{{ $kartu['judul'] }}</div>
                                <div class="display-6 fw-bold mt-2 mb-1">{{ $kartu['nilai'] }}</div>
                                <div class="text-secondary">{{ $kartu['keterangan'] }}</div>
                            </div>

                            <span class="avatar avatar-lg {{ $kartu['kelas_avatar'] }}">
                                @include('komponen.ikon_menu', ['ikon' => $kartu['ikon']])
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-12 col-xl-8">
            <div class="card kartu-hero">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="badge bg-white text-primary mb-3">{{ $heroBeranda['badge'] }}</span>
                            <h3 class="h1 text-white mb-3">{{ $heroBeranda['judul'] }}</h3>
                            <p class="text-white text-opacity-75 mb-4">
                                {{ $heroBeranda['deskripsi'] }}
                            </p>

                            <div class="row g-3">
                                @foreach ($heroBeranda['kilas'] as $kilas)
                                    <div class="col-sm-6">
                                        <div class="rounded-3 border border-white border-opacity-10 px-3 py-3 h-100">
                                            <div class="text-uppercase small fw-semibold text-white text-opacity-75">
                                                {{ $kilas['label'] }}
                                            </div>
                                            <div class="h2 text-white mt-2 mb-0">{{ $kilas['nilai'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card bg-white bg-opacity-10 border-0 shadow-none mb-0">
                                <div class="card-body">
                                    <div class="text-uppercase fw-semibold small text-white text-opacity-75 mb-2">
                                        {{ $heroBeranda['judul_panel'] }}
                                    </div>
                                    <ul class="list-unstyled list-gap-3 mb-0">
                                        @foreach ($heroBeranda['fokus'] as $fokus)
                                            <li class="d-flex align-items-start gap-3">
                                                <span class="badge {{ $fokus['kelas_badge'] }}"></span>
                                                <div class="text-white">{{ $fokus['teks'] }}</div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="d-flex flex-wrap gap-2 mt-4">
                                        @foreach ($heroBeranda['aksi'] as $aksi)
                                            <a href="{{ route($aksi['route']) }}" class="{{ $aksi['kelas'] }}">{{ $aksi['label'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ $judulIndikator }}</h3>
                </div>
                <div class="card-body">
                    @foreach ($indikatorOperasional as $indikator)
                        <div class="{{ $loop->last ? '' : 'mb-4' }}">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-secondary">{{ $indikator['label'] }}</span>
                                <span class="fw-semibold">{{ $indikator['persentase'] }}%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: {{ $indikator['persentase'] }}%"
                                    role="progressbar" aria-valuenow="{{ $indikator['persentase'] }}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                            <div class="small text-secondary mt-2">{{ $indikator['keterangan'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ $judulRingkasanLms }}</h3>
                    <div class="card-actions">
                        <a href="{{ route('lms.kursus') }}" class="btn btn-sm btn-outline-primary">Buka LMS</a>
                    </div>
                </div>
                <div class="card-body">
                    @foreach ($ringkasanLms as $item)
                        <div class="d-flex align-items-center justify-content-between py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                            <span class="text-secondary">{{ $item['label'] }}</span>
                            <span class="fw-semibold">{{ $item['nilai'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ $judulRingkasanSekunder }}</h3>
                    <div class="card-actions">
                        <a href="{{ route('tools.artikel') }}" class="btn btn-sm btn-outline-primary">Buka Tools</a>
                    </div>
                </div>
                <div class="card-body">
                    @foreach ($ringkasanSekunder as $item)
                        <div class="d-flex align-items-center justify-content-between py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                            <span class="text-secondary">{{ $item['label'] }}</span>
                            <span class="fw-semibold">{{ $item['nilai'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ $judulAksesCepat }}</h3>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($aksesCepat as $akses)
                            <div class="col-6">
                                <a href="{{ route($akses['route']) }}"
                                    class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-start gap-2 text-start">
                                    @include('komponen.ikon_menu', ['ikon' => $akses['ikon']])
                                    <span>{{ $akses['judul'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $judulAktivitas }}</h3>
                </div>

                @if ($aktivitasTerbaru->isEmpty())
                    <div class="card-body">
                        <div class="empty">
                            <p class="empty-title">Belum ada aktivitas</p>
                            <p class="empty-subtitle text-secondary mb-0">
                                Aktivitas sistem akan tampil di sini setelah ada aksi dari modul yang sudah aktif.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="list-group list-group-flush list-group-hoverable">
                        @foreach ($aktivitasTerbaru as $aktivitas)
                            <div class="list-group-item">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span class="badge {{ $aktivitas->kelasBadgeAksi() }}">{{ $aktivitas->labelAksi() }}</span>
                                            <span class="text-secondary small">{{ $aktivitas->labelModul() }}</span>
                                            @if ($aktivitas->pengguna)
                                                <span class="text-secondary small">oleh {{ $aktivitas->pengguna->name }}</span>
                                            @endif
                                        </div>
                                        <div class="fw-semibold mt-2">{{ $aktivitas->deskripsi }}</div>
                                        @if ($aktivitas->subjek_tipe)
                                            <div class="small text-secondary mt-1">{{ $aktivitas->labelSubjek() }}</div>
                                        @endif
                                    </div>
                                    <div class="small text-secondary text-nowrap">
                                        {{ $aktivitas->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $judulPrioritasTugas }}</h3>
                            <div class="card-actions">
                                <a href="{{ route('proyek.detail_tugas') }}" class="btn btn-sm btn-outline-primary">Buka Board</a>
                            </div>
                        </div>

                        @if ($prioritasTugas->isEmpty())
                            <div class="card-body">
                                <div class="empty">
                                    <p class="empty-title">Belum ada tugas prioritas</p>
                                    <p class="empty-subtitle text-secondary mb-0">
                                        {{ $pesanKosongPrioritasTugas }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($prioritasTugas as $tugas)
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div class="min-w-0">
                                                <div class="fw-semibold">{{ $tugas->judul_tugas }}</div>
                                                <div class="small text-secondary mt-1">
                                                    {{ $tugas->proyek?->nama_project ?? 'Tanpa project' }}
                                                </div>
                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    <span class="badge {{ $tugas->kelasBadgeStatusTugas() }}">
                                                        {{ $tugas->labelStatusTugas() }}
                                                    </span>
                                                    <span class="badge {{ $tugas->kelasBadgePrioritasTugas() }}">
                                                        {{ $tugas->labelPrioritasTugas() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-end small text-secondary">
                                                @if ($tugas->tanggal_target)
                                                    <div>{{ $tugas->tanggal_target->translatedFormat('d M Y') }}</div>
                                                @else
                                                    <div>Tanpa target</div>
                                                @endif

                                                @if ($pengguna->adalahSuperadmin() && $tugas->penanggungJawab)
                                                    <div class="mt-1">{{ $tugas->penanggungJawab->name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Notifikasi Terbaru</h3>
                            <div class="card-actions">
                                <a href="{{ route('notifikasi.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                            </div>
                        </div>

                        @if ($notifikasiTerbaru->isEmpty())
                            <div class="card-body">
                                <div class="empty">
                                    <p class="empty-title">Belum ada notifikasi</p>
                                    <p class="empty-subtitle text-secondary mb-0">
                                        Notifikasi pengguna akan muncul di sini saat ada aktivitas penting.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($notifikasiTerbaru as $notifikasi)
                                    <div class="list-group-item {{ $notifikasi->sudahDibaca() ? '' : 'notifikasi-item-belum' }}">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div class="min-w-0">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="badge {{ $notifikasi->kelasBadgeTipe() }}">{{ $notifikasi->labelTipe() }}</span>
                                                    @unless ($notifikasi->sudahDibaca())
                                                        <span class="indikator-belum-dibaca"></span>
                                                    @endunless
                                                </div>
                                                <div class="fw-semibold mt-2">{{ $notifikasi->judul }}</div>
                                                <div class="small text-secondary mt-1">{{ $notifikasi->pesan }}</div>
                                            </div>
                                            <div class="small text-secondary text-nowrap">
                                                {{ $notifikasi->created_at?->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
