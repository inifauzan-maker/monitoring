<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $judulHalaman }} - Link Publik</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="halaman-link-publik"
        style="
            --link-publik-background: {{ $temaHalaman['background'] }};
            --link-publik-surface: {{ $temaHalaman['surface'] }};
            --link-publik-surface-soft: {{ $temaHalaman['surface_soft'] }};
            --link-publik-text: {{ $temaHalaman['text'] }};
            --link-publik-muted: {{ $temaHalaman['muted'] }};
            --link-publik-accent-soft: {{ $temaHalaman['accent_soft'] }};
            --link-publik-border: {{ $temaHalaman['border'] }};
            --link-publik-button: {{ $temaHalaman['button'] }};
        "
    >
        <div class="container container-tight py-5">
            <div class="panel-link-publik">
                <div class="hero-link-publik">
                    <div class="avatar-link-publik-frame">
                        @if ($avatarPublikUrl)
                            <img
                                src="{{ $avatarPublikUrl }}"
                                alt="Avatar {{ $namaPublik }}"
                                class="avatar-link-publik-img"
                                onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                            >
                        @endif
                        <span class="avatar-link-publik-fallback {{ $avatarPublikUrl ? 'd-none' : '' }}">
                            {{ $pengguna->inisialLinkPublik() }}
                        </span>
                    </div>
                    @if ($menggunakanDomainKustom || $sourceAktif)
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            @if ($menggunakanDomainKustom)
                                <div class="badge-link-publik">Domain Kustom</div>
                            @endif
                            @if ($sourceAktif)
                                <div class="badge-link-publik">Source: {{ $sourceAktif }}</div>
                            @endif
                        </div>
                    @endif
                    <h1 class="judul-link-publik">{{ $namaPublik }}</h1>
                    @if ($headlineHalaman)
                        <div class="headline-link-publik">{{ $headlineHalaman }}</div>
                    @endif
                    @if ($bioHalaman)
                        <p class="deskripsi-link-publik mx-auto mb-0">
                            {{ $bioHalaman }}
                        </p>
                    @endif
                    @if ($nomorWaPublik)
                        <div class="kontak-link-publik">
                            <span class="badge-link-publik">WhatsApp</span>
                            <div class="fw-semibold mt-2">{{ $nomorWaPublik }}</div>
                        </div>
                    @endif
                </div>

                @if ($urlCta || $urlWaPublik)
                    <div class="grup-aksi-link-publik mt-4">
                        @if ($urlCta)
                            <a href="{{ $urlCtaPublik }}" class="tombol-cta-link-publik">
                                {{ $labelCta }}
                            </a>
                        @endif
                        @if ($urlWaPublik)
                            <a href="{{ $urlWaPublik }}" class="tombol-wa-link-publik" target="_blank" rel="noopener noreferrer">
                                Chat WhatsApp
                            </a>
                        @endif
                    </div>
                @endif

                <div class="mt-4 d-grid gap-3">
                    @forelse ($linkAktif as $link)
                        <a
                            href="{{ $link->url_publik }}"
                            class="item-link-publik text-decoration-none"
                        >
                            <div class="fw-semibold fs-3">{{ $link->judul }}</div>
                            @if ($link->deskripsi)
                                <div class="deskripsi-link-publik mt-2">{{ $link->deskripsi }}</div>
                            @endif
                            <div class="badge-link-publik mt-3">Buka Link</div>
                        </a>
                    @empty
                        <div class="item-link-publik text-center">
                            <p class="fw-semibold mb-2">Belum ada link aktif</p>
                            <p class="deskripsi-link-publik mb-0">
                                Halaman publik ini belum memiliki link yang ditampilkan.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="text-center small mt-4" style="color: var(--link-publik-muted);">
                    Dikelola melalui {{ config('app.name', 'Simarketing') }}
                </div>
            </div>
        </div>
    </body>
</html>
