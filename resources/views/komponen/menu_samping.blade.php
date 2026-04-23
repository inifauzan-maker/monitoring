@php
    $penggunaMasuk = auth()->user();
    $bagianMenu = collect(config('menu_samping', []));
@endphp

<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-samping"
            aria-controls="menu-samping" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="d-flex align-items-center justify-content-between gap-2 w-100">
            <a class="navbar-brand py-3 flex-grow-1" href="{{ route('dashboard.beranda') }}">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-rounded bg-primary text-primary-fg">
                        <svg class="icon icon-tabler icon-tabler-chart-donut-3" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 3v5m0 4v9" />
                            <path d="M5 12h5m4 0h5" />
                            <path d="M7.5 7.5l3.5 3.5" />
                            <path d="M13 13l3.5 3.5" />
                            <path d="M16.5 7.5l-3.5 3.5" />
                            <path d="M11 13l-3.5 3.5" />
                        </svg>
                    </span>

                    <div class="wadah-teks-sidebar">
                        <div class="fw-bold teks-judul-sidebar">{{ config('app.name', 'Monitoring') }}</div>
                        <div class="small teks-subjudul-sidebar">Dashboard aplikasi</div>
                    </div>
                </div>
            </a>

            <button
                class="btn btn-icon btn-ghost-secondary d-none d-lg-inline-flex tombol-toggle-sidebar"
                type="button"
                data-aksi="ubah-sidebar"
                aria-label="Ciutkan sidebar"
                title="Ciutkan sidebar"
            >
                <svg class="icon ikon-sidebar-ciut" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M15 6l-6 6l6 6" />
                </svg>
                <svg class="icon ikon-sidebar-buka d-none" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M9 6l6 6l-6 6" />
                </svg>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="menu-samping">
            <ul class="navbar-nav pt-lg-3">
                @foreach ($bagianMenu as $indeksBagian => $bagian)
                    @php
                        $itemTerlihat = collect($bagian['items'] ?? [])
                            ->map(function (array $item) use ($penggunaMasuk) {
                                $item['punya_submenu'] = array_key_exists('anak', $item);
                                $item['anak'] = collect($item['anak'] ?? [])
                                    ->filter(fn (array $anak) => $penggunaMasuk?->punyaAksesMinimal($anak['level_minimal'] ?? $item['level_minimal'] ?? 'level_5'))
                                    ->values()
                                    ->all();

                                return $item;
                            })
                            ->filter(function (array $item) use ($penggunaMasuk) {
                                if (! $penggunaMasuk?->punyaAksesMinimal($item['level_minimal'] ?? 'level_5')) {
                                    return false;
                                }

                                return ! $item['punya_submenu'] || count($item['anak']) > 0;
                            })
                            ->values();
                    @endphp

                    @continue($itemTerlihat->isEmpty() && ! ($bagian['selalu_tampil'] ?? false))

                    @if ($indeksBagian > 0)
                        <li class="nav-item mt-4">
                            <div class="nav-link text-uppercase text-secondary fw-semibold small">{{ $bagian['bagian'] }}</div>
                        </li>
                    @endif

                    @foreach ($itemTerlihat as $item)
                        @php
                            $punyaAnak = ($item['punya_submenu'] ?? false) && ! empty($item['anak'] ?? []);
                            $apakahAktif = collect($item['aktif'] ?? [])->contains(fn (string $pola) => request()->routeIs($pola));

                            if ($punyaAnak) {
                                $apakahAktif = $apakahAktif || collect($item['anak'])->contains(function (array $anak) {
                                    return collect($anak['aktif'] ?? [])->contains(fn (string $pola) => request()->routeIs($pola));
                                });
                            }

                            $punyaRoute = filled($item['route'] ?? null) && Route::has($item['route']);
                        @endphp

                        @if ($punyaAnak)
                            @php($idSubmenu = 'submenu-'.\Illuminate\Support\Str::slug($item['judul']))
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle {{ $apakahAktif ? 'active' : '' }}" href="#{{ $idSubmenu }}"
                                    data-bs-toggle="collapse" data-submenu-toggle role="button"
                                    aria-expanded="{{ $apakahAktif ? 'true' : 'false' }}"
                                    aria-controls="{{ $idSubmenu }}" title="{{ $item['judul'] }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        @include('komponen.ikon_menu', ['ikon' => $item['ikon']])
                                    </span>
                                    <span class="nav-link-title">{{ $item['judul'] }}</span>
                                </a>

                                <div class="collapse {{ $apakahAktif ? 'show' : '' }}" id="{{ $idSubmenu }}">
                                    <ul class="navbar-nav ps-3">
                                        @foreach ($item['anak'] as $anak)
                                            @php($anakAktif = collect($anak['aktif'] ?? [])->contains(fn (string $pola) => request()->routeIs($pola)))
                                            <li class="nav-item">
                                                <a class="nav-link {{ $anakAktif ? 'active' : '' }}" href="{{ route($anak['route']) }}" title="{{ $anak['judul'] }}">
                                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                                        @include('komponen.ikon_menu', ['ikon' => $anak['ikon']])
                                                    </span>
                                                    <span class="nav-link-title">{{ $anak['judul'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @elseif ($punyaRoute)
                            <li class="nav-item">
                                <a class="nav-link {{ $apakahAktif ? 'active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['judul'] }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        @include('komponen.ikon_menu', ['ikon' => $item['ikon']])
                                    </span>
                                    <span class="nav-link-title">{{ $item['judul'] }}</span>
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <span class="nav-link disabled" title="{{ $item['judul'] }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        @include('komponen.ikon_menu', ['ikon' => $item['ikon']])
                                    </span>
                                    <span class="nav-link-title">{{ $item['judul'] }}</span>
                                    @if (! empty($item['status']))
                                        <span class="badge bg-secondary-lt ms-auto">{{ $item['status'] }}</span>
                                    @endif
                                </span>
                            </li>
                        @endif
                    @endforeach
                @endforeach
            </ul>

            <div class="mt-auto pt-4 pb-3 d-none d-lg-block">
                <div class="card card-sm sidebar-note">
                    <div class="card-body">
                        <div class="text-uppercase text-secondary fw-semibold small mb-2">Catatan</div>
                        <p class="text-secondary mb-0">
                            Menu sekarang mengikuti level akses pengguna dan sudah mendukung submenu untuk modul yang
                            memiliki beberapa kanal kerja.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
