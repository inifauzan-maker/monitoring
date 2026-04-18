@extends('tataletak.aplikasi')

@section('judul_halaman', 'Dasbor Monitoring')
@section('pratitel_halaman', 'Ikhtisar')
@section('judul_konten', 'Dasbor Monitoring')
@section('deskripsi_halaman', 'Dashboard sekarang sudah memakai login dan pondasi RBAC berbasis superadmin serta level 1 sampai level 5.')

@section('aksi_halaman')
    <div class="btn-list">
        <span class="badge {{ auth()->user()->kelasBadgeLevelAkses() }} px-3 py-2">
            {{ auth()->user()->labelLevelAkses() }}
        </span>

        @if (auth()->user()->adalahSuperadmin())
            <a href="{{ route('pengaturan.pengguna.index') }}" class="btn btn-primary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    <path d="M19 8l2 2l-2 2" />
                    <path d="M21 10h-4" />
                </svg>
                Pengaturan Pengguna
            </a>
        @endif
    </div>
@endsection

@section('konten')
    <div class="row row-deck row-cards">
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan kartu-ringkasan-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small">Perangkat dipantau</div>
                            <div class="display-6 fw-bold mt-2 mb-1">128</div>
                            <div class="text-secondary">Simulasi data awal dashboard</div>
                        </div>
                        <span class="avatar avatar-lg bg-primary-lt text-primary">
                            <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 17l0 2" />
                                <path d="M12 17l0 2" />
                                <path d="M15 17l0 2" />
                                <path d="M5 5l14 0" />
                                <path d="M5 9l14 0" />
                                <path d="M5 13l14 0" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small">Lokasi aktif</div>
                            <div class="display-6 fw-bold mt-2 mb-1">12</div>
                            <div class="text-secondary">Cabang dan titik operasional</div>
                        </div>
                        <span class="avatar avatar-lg bg-azure-lt text-azure">
                            <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 21l-8 -4v-6l8 -4l8 4v6z" />
                                <path d="M12 12l8 -4" />
                                <path d="M12 12v9" />
                                <path d="M12 12l-8 -4" />
                                <path d="M4 11v6" />
                                <path d="M20 11v6" />
                                <path d="M12 3v4" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small">Insiden hari ini</div>
                            <div class="display-6 fw-bold mt-2 mb-1">3</div>
                            <div class="text-secondary">Prioritas tinggi untuk ditinjau</div>
                        </div>
                        <span class="avatar avatar-lg bg-orange-lt text-orange">
                            <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 9v4" />
                                <path d="M12 17h.01" />
                                <path
                                    d="M5.07 19h13.86c1.54 0 2.5 -1.67 1.73 -3l-6.93 -12c-.77 -1.33 -2.69 -1.33 -3.46 0l-6.93 12c-.77 1.33 .19 3 1.73 3z" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small">SLA bulanan</div>
                            <div class="display-6 fw-bold mt-2 mb-1">98,4%</div>
                            <div class="text-secondary">Indikator performa layanan</div>
                        </div>
                        <span class="avatar avatar-lg bg-green-lt text-green">
                            <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 17l6 -6l4 4l8 -8" />
                                <path d="M14 7l7 0l0 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card kartu-hero">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <span class="badge bg-white text-primary mb-3">Tema Tabler aktif</span>
                            <h3 class="h1 text-white mb-3">Dashboard dasar dan RBAC sudah siap dipakai.</h3>
                            <p class="text-white text-opacity-75 mb-4">
                                Sistem sudah memiliki autentikasi dasar, level akses berjenjang, dan halaman pengaturan
                                pengguna. Langkah berikutnya tinggal menyusun modul serta membatasi akses tiap halaman sesuai
                                kebutuhan bisnis.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="btn btn-light disabled">Superadmin dan level 1-5 aktif</span>
                                <span class="btn btn-outline-light disabled">Menu modul bisa dilanjutkan berikutnya</span>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card bg-white bg-opacity-10 border-0 shadow-none mb-0">
                                <div class="card-body">
                                    <div class="text-uppercase fw-semibold small text-white text-opacity-75 mb-2">
                                        Area kerja
                                    </div>
                                    <ul class="list-unstyled list-gap-3 mb-0">
                                        <li class="d-flex align-items-start gap-3">
                                            <span class="badge bg-success"></span>
                                            <div class="text-white">Login dan logout dasar sudah aktif.</div>
                                        </li>
                                        <li class="d-flex align-items-start gap-3">
                                            <span class="badge bg-warning"></span>
                                            <div class="text-white">Pengaturan pengguna hanya bisa diakses superadmin.</div>
                                        </li>
                                        <li class="d-flex align-items-start gap-3">
                                            <span class="badge bg-info"></span>
                                            <div class="text-white">Modul berikutnya bisa diberi middleware level akses.</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status sistem</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Sinkronisasi data</span>
                            <span class="fw-semibold">92%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: 92%" role="progressbar"
                                aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Kesiapan notifikasi</span>
                            <span class="fw-semibold">76%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-orange" style="width: 76%" role="progressbar"
                                aria-valuenow="76" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Kelengkapan modul</span>
                            <span class="fw-semibold">41%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-azure" style="width: 41%" role="progressbar"
                                aria-valuenow="41" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas terbaru</h3>
                </div>
                <div class="list-group list-group-flush list-group-hoverable">
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="status-dot status-dot-animated bg-green d-block"></span>
                            </div>
                            <div class="col text-truncate">
                                <span class="text-body d-block">Tema Tabler berhasil dipasang ke aplikasi.</span>
                                <div class="d-block text-secondary text-truncate mt-n1">Asset CSS dan JS kini memakai Tabler.</div>
                            </div>
                            <div class="col-auto text-secondary">Baru saja</div>
                        </div>
                    </div>

                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="status-dot bg-azure d-block"></span>
                            </div>
                            <div class="col text-truncate">
                                <span class="text-body d-block">RBAC pengguna telah ditambahkan.</span>
                                <div class="d-block text-secondary text-truncate mt-n1">Tersedia superadmin dan level 1-5.</div>
                            </div>
                            <div class="col-auto text-secondary">Tahap awal</div>
                        </div>
                    </div>

                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="status-dot bg-orange d-block"></span>
                            </div>
                            <div class="col text-truncate">
                                <span class="text-body d-block">Menu modul belum disusun permanen.</span>
                                <div class="d-block text-secondary text-truncate mt-n1">Placeholder sengaja disisakan untuk tahap berikutnya.</div>
                            </div>
                            <div class="col-auto text-secondary">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ruang pengembangan</h3>
                </div>
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-img">
                            <div class="avatar avatar-xl bg-primary-lt text-primary mx-auto">
                                <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 3l9 4.5v9l-9 4.5l-9 -4.5v-9z" />
                                    <path d="M12 12l9 -4.5" />
                                    <path d="M12 12v9" />
                                    <path d="M12 12l-9 -4.5" />
                                </svg>
                            </div>
                        </div>
                        <p class="empty-title">Siap untuk modul berikutnya</p>
                        <p class="empty-subtitle text-secondary">
                            Anda bisa melanjutkan dengan menambahkan modul operasional, lalu membatasi aksesnya menggunakan
                            middleware level akses yang sudah tersedia.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
