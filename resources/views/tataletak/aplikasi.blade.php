<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('judul_halaman', 'Dasbor') - {{ config('app.name', 'Monitoring') }}</title>

        <script>
            (() => {
                const tema = localStorage.getItem('tema_aplikasi');

                if (tema) {
                    document.documentElement.setAttribute('data-bs-theme', tema);
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @php
            $penggunaMasuk = auth()->user();
            $notifikasiRingkas = $penggunaMasuk?->notifikasiPengguna()->latest()->limit(5)->get() ?? collect();
            $jumlahNotifikasiBelumDibaca = $penggunaMasuk?->notifikasiPengguna()->whereNull('dibaca_pada')->count() ?? 0;
        @endphp

        <div class="page">
            @include('komponen.menu_samping')

            <div class="page-wrapper">
                <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
                    <div class="container-xl">
                        <div class="navbar-nav flex-row order-md-last align-items-center gap-2">
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link px-2 position-relative" data-bs-toggle="dropdown"
                                    aria-label="Lihat notifikasi" title="Lihat notifikasi">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 5a2 2 0 1 1 4 0v.01" />
                                        <path d="M12 21a2 2 0 0 0 2 -2h-4a2 2 0 0 0 2 2z" />
                                        <path d="M5 17h14" />
                                        <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9h-18s3 -2 3 -9" />
                                    </svg>
                                    @if ($jumlahNotifikasiBelumDibaca > 0)
                                        <span class="badge bg-red badge-notifikasi">
                                            {{ $jumlahNotifikasiBelumDibaca > 9 ? '9+' : $jumlahNotifikasiBelumDibaca }}
                                        </span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-notifikasi p-0">
                                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                                        <div>
                                            <div class="fw-semibold">Notifikasi</div>
                                            <div class="small text-secondary">
                                                {{ $jumlahNotifikasiBelumDibaca }} belum dibaca
                                            </div>
                                        </div>
                                        <a href="{{ route('notifikasi.index') }}" class="small fw-semibold text-decoration-none">
                                            Lihat Semua
                                        </a>
                                    </div>

                                    @if ($notifikasiRingkas->isEmpty())
                                        <div class="px-3 py-4 text-center text-secondary small">
                                            Belum ada notifikasi baru.
                                        </div>
                                    @else
                                        <div class="list-group list-group-flush">
                                            @foreach ($notifikasiRingkas as $notifikasi)
                                                <div class="list-group-item px-3 py-3 {{ $notifikasi->sudahDibaca() ? '' : 'notifikasi-item-belum' }}">
                                                    <div class="d-flex gap-3">
                                                        <div class="flex-shrink-0 pt-1">
                                                            @include('komponen.ikon_notifikasi', [
                                                                'tipe' => $notifikasi->tipe,
                                                                'kelas' => $notifikasi->kelasIkonTipe(),
                                                            ])
                                                        </div>
                                                        <div class="flex-grow-1 min-w-0">
                                                            <div class="d-flex align-items-start justify-content-between gap-2">
                                                                <div class="fw-semibold text-body">{{ $notifikasi->judul }}</div>
                                                                @unless ($notifikasi->sudahDibaca())
                                                                    <span class="indikator-belum-dibaca"></span>
                                                                @endunless
                                                            </div>
                                                            <div class="small text-secondary mt-1">
                                                                {{ \Illuminate\Support\Str::limit($notifikasi->pesan, 96) }}
                                                            </div>
                                                            <div class="small text-secondary mt-2">
                                                                {{ $notifikasi->created_at?->diffForHumans() }}
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                @unless ($notifikasi->sudahDibaca())
                                                                    <form action="{{ route('notifikasi.baca', $notifikasi) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="redirect_ke" value="{{ $notifikasi->tautan ?: route('notifikasi.index') }}">
                                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                                            {{ $notifikasi->tautan ? 'Buka' : 'Tandai Dibaca' }}
                                                                        </button>
                                                                    </form>
                                                                @endunless

                                                                @if ($notifikasi->tautan)
                                                                    <a href="{{ $notifikasi->tautan }}" class="btn btn-sm btn-outline-secondary">
                                                                        Tujuan
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center gap-2 px-3 py-2 border-top">
                                        <form action="{{ route('notifikasi.baca_semua') }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="redirect_ke" value="{{ route('notifikasi.index') }}">
                                            <button type="submit" class="btn btn-sm btn-ghost-secondary">
                                                Baca Semua
                                            </button>
                                        </form>

                                        <a href="{{ route('notifikasi.index') }}" class="btn btn-sm btn-outline-primary">
                                            Buka Halaman
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <button
                                class="btn btn-icon btn-ghost-secondary"
                                type="button"
                                data-aksi="ubah-tema"
                                aria-label="Ubah tema"
                                title="Ubah tema"
                            >
                                <svg class="icon ikon-mode-gelap" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 3c.132 0 .263 0 .393 .01a9 9 0 1 0 8.597 11.36a7 7 0 1 1 -8.99 -11.37z" />
                                </svg>
                                <svg class="icon ikon-mode-terang d-none" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M3 12h1" />
                                    <path d="M12 3v1" />
                                    <path d="M5.6 5.6l.7 .7" />
                                    <path d="M18.4 5.6l-.7 .7" />
                                    <path d="M21 12h-1" />
                                    <path d="M12 21v-1" />
                                    <path d="M18.4 18.4l-.7 -.7" />
                                    <path d="M5.6 18.4l.7 -.7" />
                                </svg>
                            </button>

                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                                    aria-label="Buka profil">
                                    <span class="avatar avatar-sm rounded-circle avatar-app">
                                        {{ str($penggunaMasuk?->name ?? 'MO')->upper()->substr(0, 2) }}
                                    </span>
                                    <div class="d-none d-xl-block ps-2">
                                        <div class="fw-semibold">{{ $penggunaMasuk?->name }}</div>
                                        <div class="mt-1 small text-secondary">{{ $penggunaMasuk?->labelLevelAkses() }}</div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <span class="dropdown-item-text">
                                        <div class="fw-semibold">{{ $penggunaMasuk?->name }}</div>
                                        <div class="small text-secondary">{{ $penggunaMasuk?->email }}</div>
                                    </span>

                                    @if ($penggunaMasuk?->adalahSuperadmin())
                                        <a href="{{ route('pengaturan.pengguna.index') }}" class="dropdown-item">
                                            Pengaturan Pengguna
                                        </a>
                                    @endif

                                    <div class="dropdown-divider"></div>

                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="collapse navbar-collapse">
                            <div class="search-box">
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                            <path d="M21 21l-6 -6" />
                                        </svg>
                                    </span>
                                    <input type="text" value="" class="form-control" placeholder="Cari modul, lokasi, atau data..." aria-label="Cari">
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                @hasSection('pratitel_halaman')
                                    <div class="page-pretitle">@yield('pratitel_halaman')</div>
                                @endif

                                <h2 class="page-title">@yield('judul_konten', 'Dasbor')</h2>

                                @hasSection('deskripsi_halaman')
                                    <div class="text-secondary mt-1">@yield('deskripsi_halaman')</div>
                                @endif
                            </div>

                            <div class="col-auto ms-auto d-print-none">
                                @yield('aksi_halaman')
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="container-xl mt-3">
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="container-xl mt-3">
                        <div class="alert alert-danger" role="alert">
                            <div class="fw-semibold mb-2">Ada data yang perlu diperbaiki.</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="page-body">
                    <div class="container-xl">
                        @yield('konten')
                    </div>
                </div>
            </div>
        </div>

        @stack('skrip_halaman')
    </body>
</html>
