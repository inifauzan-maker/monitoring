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
                <div class="text-center">
                    <div class="avatar avatar-xl avatar-app mx-auto mb-3">
                        {{ str($pengguna->name)->upper()->substr(0, 2) }}
                    </div>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <div class="badge-link-publik">Link Publik</div>
                        <div class="badge-link-publik">{{ $temaHalaman['label'] }}</div>
                        @if ($menggunakanDomainKustom)
                            <div class="badge-link-publik">Domain Kustom</div>
                        @endif
                        @if ($sourceAktif)
                            <div class="badge-link-publik">Source: {{ $sourceAktif }}</div>
                        @endif
                    </div>
                    <h1 class="mt-3 mb-2">{{ $judulHalaman }}</h1>
                    @if ($headlineHalaman)
                        <div class="headline-link-publik">{{ $headlineHalaman }}</div>
                    @endif
                    @if ($bioHalaman)
                        <p class="deskripsi-link-publik mx-auto mt-3">
                            {{ $bioHalaman }}
                        </p>
                    @endif
                </div>

                @if ($urlCta)
                    <div class="mt-4">
                        <a href="{{ $urlCtaPublik }}" class="tombol-cta-link-publik">
                            {{ $labelCta }}
                        </a>
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
                    Dikelola melalui Monitoring
                </div>
            </div>
        </div>
    </body>
</html>
