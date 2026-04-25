@extends('tataletak.aplikasi')

@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Link')
@section('deskripsi_halaman', 'Kelola daftar link seperti modul referensi landing page: tambah, urutkan, aktifkan, analitikkan source traffic, dan siapkan domain kustom.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ $urlPublikUtama }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
            Buka Halaman Publik
        </a>
        <a href="{{ $urlExportCsv }}" class="btn btn-outline-secondary">
            Export CSV
        </a>
        <a href="{{ $urlExportPdf }}" class="btn btn-outline-secondary">
            Export PDF
        </a>
    </div>
@endsection

@section('konten')
    @php
        $punyaLink = $items->isNotEmpty();
    @endphp

    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Klik</div>
                    <div class="mt-2 fs-1 fw-bold text-primary">{{ number_format($totalKlik) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Link</div>
                    <div class="mt-2 fs-1 fw-bold">{{ $totalLink }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Link Aktif</div>
                    <div class="mt-2 fs-1 fw-bold text-success">{{ $totalAktif }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Link Nonaktif</div>
                    <div class="mt-2 fs-1 fw-bold text-warning">{{ $totalNonaktif }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Kunjungan {{ $analitik['rentang_hari'] }} Hari</div>
                    <div class="mt-2 fs-1 fw-bold text-info">{{ number_format($analitik['metrik']['kunjungan_halaman']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Klik CTA {{ $analitik['rentang_hari'] }} Hari</div>
                    <div class="mt-2 fs-1 fw-bold text-secondary">{{ number_format($analitik['metrik']['klik_cta']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Rasio Interaksi</div>
                    <div class="mt-2 fs-1 fw-bold text-success">{{ number_format($analitik['metrik']['rasio_interaksi'], 1) }}%</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card kartu-ringkasan">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Source Aktif</div>
                    <div class="mt-2 fw-bold">{{ $analitik['source_label'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Link Publik</div>
                        <h3 class="card-title mt-1">Profil publik seperti landing page</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="border rounded-3 p-3 bg-body-secondary mb-3">
                        <div class="small text-secondary">URL publik utama</div>
                        <div class="fw-semibold text-break mt-1">{{ $urlPublikUtama }}</div>
                        <div class="btn-list mt-3">
                            <a href="{{ $urlPublikUtama }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                Buka
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy-text="{{ $urlPublikUtama }}">
                                Salin
                            </button>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-3">
                        <div class="small text-secondary">URL default aplikasi</div>
                        <div class="fw-semibold text-break mt-1">{{ $urlPublikDefault }}</div>
                    </div>

                    @if ($urlPublikDomainKustom)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between gap-3 align-items-start">
                                <div>
                                    <div class="small text-secondary">URL domain kustom</div>
                                    <div class="fw-semibold text-break mt-1">{{ $urlPublikDomainKustom }}</div>
                                </div>
                                <span class="badge {{ $pengguna->domain_kustom_terhubung_pada ? 'bg-success-lt text-success' : 'bg-warning-lt text-warning' }}">
                                    {{ $pengguna->domain_kustom_terhubung_pada ? 'Terhubung' : 'Menunggu DNS' }}
                                </span>
                            </div>
                            <div class="form-hint mt-2">
                                @if ($pengguna->domain_kustom_terhubung_pada)
                                    Domain kustom sudah pernah diakses dan terdeteksi aktif pada {{ $pengguna->domain_kustom_terhubung_pada->format('d M Y H:i') }}.
                                @else
                                    Arahkan domain/subdomain ke <strong>{{ $targetDomainKustom }}</strong>, lalu buka domain tersebut untuk menandai status terhubung.
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="border rounded-3 p-3 mb-3">
                        <div class="small text-secondary mb-2">Preview identitas publik</div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-link-publik-frame avatar-link-publik-frame-mini flex-shrink-0">
                                @if ($pengguna->avatarLinkPublikUrl())
                                    <img
                                        src="{{ $pengguna->avatarLinkPublikUrl() }}"
                                        alt="Avatar {{ $pengguna->namaTampilLinkPublik() }}"
                                        class="avatar-link-publik-img"
                                        onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                                    >
                                @endif
                                <span class="avatar-link-publik-fallback {{ $pengguna->avatarLinkPublikUrl() ? 'd-none' : '' }}">
                                    {{ $pengguna->inisialLinkPublik() }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold">{{ $pengguna->namaTampilLinkPublik() }}</div>
                                <div class="text-secondary small text-break mt-1">{{ $pengguna->judulLinkPublik() }}</div>
                                @if ($pengguna->nomorWaLinkTampil())
                                    <div class="text-secondary small text-break mt-1">WhatsApp: {{ $pengguna->nomorWaLinkTampil() }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tools.link.profil.update') }}" class="d-grid gap-3" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="nama_tampil_link" class="form-label">Nama publik</label>
                            <input
                                id="nama_tampil_link"
                                type="text"
                                name="nama_tampil_link"
                                value="{{ old('nama_tampil_link', $pengguna->nama_tampil_link) }}"
                                class="form-control"
                                placeholder="Nama yang tampil di halaman link publik"
                            >
                        </div>

                        <div>
                            <label for="nomor_wa_link" class="form-label">Nomor WhatsApp</label>
                            <input
                                id="nomor_wa_link"
                                type="text"
                                name="nomor_wa_link"
                                value="{{ old('nomor_wa_link', $pengguna->nomor_wa_link) }}"
                                class="form-control"
                                placeholder="081234567890 atau 6281234567890"
                            >
                            <div class="form-hint">Nomor ini bisa ditampilkan dan dibuka langsung ke WhatsApp pada halaman publik.</div>
                        </div>

                        <div>
                            <label for="slug_link" class="form-label">Slug publik</label>
                            <div class="input-group">
                                <span class="input-group-text">/u/</span>
                                <input
                                    id="slug_link"
                                    type="text"
                                    name="slug_link"
                                    value="{{ old('slug_link', $pengguna->slug_link) }}"
                                    class="form-control"
                                    required
                                >
                            </div>
                        </div>

                        <div>
                            <label for="domain_kustom_link" class="form-label">Domain kustom</label>
                            <input
                                id="domain_kustom_link"
                                type="text"
                                name="domain_kustom_link"
                                value="{{ old('domain_kustom_link', $pengguna->domain_kustom_link) }}"
                                class="form-control"
                                placeholder="link.monitoringanda.com"
                            >
                            <div class="form-hint">
                                Opsional. Gunakan subdomain/domain khusus lalu arahkan DNS ke <strong>{{ $targetDomainKustom }}</strong>.
                            </div>
                        </div>

                        <div>
                            <label for="judul_link" class="form-label">Judul browser/halaman</label>
                            <input
                                id="judul_link"
                                type="text"
                                name="judul_link"
                                value="{{ old('judul_link', $pengguna->judul_link) }}"
                                class="form-control"
                                placeholder="Opsional, untuk title halaman atau nama brand"
                            >
                        </div>

                        <div>
                            <label for="headline_link" class="form-label">Headline publik</label>
                            <input
                                id="headline_link"
                                type="text"
                                name="headline_link"
                                value="{{ old('headline_link', $pengguna->headline_link) }}"
                                class="form-control"
                            >
                        </div>

                        <div>
                            <label for="bio_link" class="form-label">Bio singkat</label>
                            <textarea id="bio_link" name="bio_link" rows="3" class="form-control">{{ old('bio_link', $pengguna->bio_link) }}</textarea>
                        </div>

                        <div>
                            <label for="label_cta_link" class="form-label">Label CTA</label>
                            <input
                                id="label_cta_link"
                                type="text"
                                name="label_cta_link"
                                value="{{ old('label_cta_link', $pengguna->label_cta_link) }}"
                                class="form-control"
                                placeholder="Buka Tautan Utama"
                            >
                        </div>

                        <div>
                            <label for="avatar_link" class="form-label">Avatar publik</label>
                            <input
                                id="avatar_link"
                                type="file"
                                name="avatar_link"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="form-control"
                            >
                            <div class="form-hint">Format: JPG, PNG, atau WebP. Maksimal 2 MB.</div>
                            @if ($pengguna->avatar_link)
                                <label class="form-check mt-2 mb-0">
                                    <input class="form-check-input" type="checkbox" name="hapus_avatar_link" value="1" @checked(old('hapus_avatar_link'))>
                                    <span class="form-check-label">Hapus avatar saat simpan</span>
                                </label>
                            @endif
                        </div>

                        <div>
                            <label for="url_cta_link" class="form-label">URL CTA</label>
                            <input
                                id="url_cta_link"
                                type="text"
                                name="url_cta_link"
                                value="{{ old('url_cta_link', $pengguna->url_cta_link) }}"
                                class="form-control"
                                placeholder="https://example.com atau example.com"
                            >
                            <div class="form-hint">CTA utama pada halaman publik akan diarahkan ke URL ini.</div>
                        </div>

                        <div>
                            <label for="tema_link" class="form-label">Tema halaman publik</label>
                            <select id="tema_link" name="tema_link" class="form-select">
                                @foreach (\App\Models\User::opsiTemaLink() as $kodeTema => $tema)
                                    <option value="{{ $kodeTema }}" @selected(old('tema_link', $pengguna->tema_link) === $kodeTema)>
                                        {{ $tema['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">Simpan Profil Publik</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Tambah Link</div>
                        <h3 class="card-title mt-1">Link edukasi, promo, atau marketplace</h3>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tools.link.store') }}" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label for="judul" class="form-label">Judul link</label>
                            <input id="judul" type="text" name="judul" value="{{ old('judul') }}" class="form-control" required>
                        </div>

                        <div>
                            <label for="deskripsi" class="form-label">Deskripsi singkat</label>
                            <input id="deskripsi" type="text" name="deskripsi" value="{{ old('deskripsi') }}" class="form-control">
                        </div>

                        <div>
                            <label for="url" class="form-label">URL tujuan</label>
                            <input
                                id="url"
                                type="text"
                                name="url"
                                value="{{ old('url') }}"
                                class="form-control"
                                placeholder="https://example.com atau example.com"
                                required
                            >
                            <div class="form-hint">Boleh isi tanpa `https://`. Sistem akan menambahkan otomatis jika perlu.</div>
                        </div>

                        <div>
                            <label for="urutan" class="form-label">Urutan</label>
                            <input id="urutan" type="number" min="0" max="999" name="urutan" value="{{ old('urutan', 0) }}" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Tambah Link Baru</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Statistik Link Harian</div>
                        <h3 class="card-title mt-1">Kunjungan, CTA, dan klik link per hari</h3>
                    </div>
                    <form method="GET" action="{{ route('tools.link') }}" class="row g-2 align-items-end w-100 w-lg-auto">
                        <div class="col-sm-4">
                            <label for="rentang" class="form-label mb-1">Rentang</label>
                            <select id="rentang" name="rentang" class="form-select">
                                @foreach ($analitik['opsi_rentang'] as $opsiRentang)
                                    <option value="{{ $opsiRentang }}" @selected($analitik['rentang_hari'] === $opsiRentang)>
                                        {{ $opsiRentang }} Hari
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-5">
                            <label for="source" class="form-label mb-1">Source Traffic</label>
                            <select id="source" name="source" class="form-select">
                                <option value="">Semua source</option>
                                @foreach ($analitik['sumber_tersedia'] as $sumber)
                                    <option value="{{ $sumber['label'] }}" @selected($analitik['source_filter'] === $sumber['label'])>
                                        {{ $sumber['label'] }} ({{ $sumber['count'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 d-grid">
                            <button type="submit" class="btn btn-primary">Terapkan</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary-lt text-primary">Periode: {{ $analitik['periode_label'] }}</span>
                        <span class="badge bg-secondary-lt text-secondary">Source: {{ $analitik['source_label'] }}</span>
                        <a href="{{ route('tools.link') }}" class="badge bg-warning-lt text-warning text-decoration-none">Atur Ulang</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-end">Kunjungan</th>
                                    <th class="text-end">Klik CTA</th>
                                    <th class="text-end">Klik Link</th>
                                    <th style="width: 10rem;">Intensitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($analitik['harian'] as $hari)
                                    @php($persentaseBar = $analitik['maksimum_harian'] > 0 ? (max($hari['kunjungan_halaman'], $hari['klik_cta'], $hari['klik_link']) / $analitik['maksimum_harian']) * 100 : 0)
                                    <tr>
                                        <td>{{ $hari['label'] }}</td>
                                        <td class="text-end">{{ number_format($hari['kunjungan_halaman']) }}</td>
                                        <td class="text-end">{{ number_format($hari['klik_cta']) }}</td>
                                        <td class="text-end">{{ number_format($hari['klik_link']) }}</td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: {{ round($persentaseBar, 1) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Traffic Source</div>
                        <h3 class="card-title mt-1">Sumber trafik halaman publik pada periode ini</h3>
                    </div>
                </div>
                <div class="card-body">
                    @if ($analitik['top_sumber']->isEmpty())
                        <div class="empty py-4">
                            <p class="empty-title">Belum ada source traffic</p>
                            <p class="empty-subtitle text-secondary">
                                Source akan terdeteksi dari parameter `source`, `utm_source`, atau referer pengunjung.
                            </p>
                        </div>
                    @else
                        <div class="d-grid gap-3">
                            @foreach ($analitik['top_sumber'] as $sumber)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between gap-3 align-items-start">
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-break">{{ $sumber['label'] }}</div>
                                            <div class="text-secondary small mt-1">{{ number_format($sumber['count']) }} kunjungan</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold text-primary">{{ number_format($sumber['share'], 1) }}%</div>
                                        </div>
                                    </div>
                                    <div class="progress progress-sm mt-3">
                                        <div class="progress-bar bg-info" style="width: {{ $sumber['share'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Top Link</div>
                        <h3 class="card-title mt-1">Link yang paling sering diklik pada periode ini</h3>
                    </div>
                </div>
                <div class="card-body">
                    @if ($analitik['top_link']->isEmpty())
                        <div class="empty py-4">
                            <p class="empty-title">Belum ada data top link</p>
                            <p class="empty-subtitle text-secondary">
                                Top link akan muncul setelah halaman publik mulai menerima klik.
                            </p>
                        </div>
                    @else
                        <div class="d-grid gap-3">
                            @foreach ($analitik['top_link'] as $itemTop)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between gap-3 align-items-start">
                                        <div class="min-w-0">
                                            <div class="fw-semibold">{{ $itemTop['link']->judul }}</div>
                                            <div class="text-secondary small text-truncate mt-1">{{ $itemTop['link']->urlTujuan() }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold text-primary">{{ number_format($itemTop['total_klik']) }} klik</div>
                                            <div class="small text-secondary">{{ number_format($itemTop['porsi_klik'], 1) }}%</div>
                                        </div>
                                    </div>
                                    <div class="progress progress-sm mt-3">
                                        <div class="progress-bar bg-primary" style="width: {{ $itemTop['porsi_klik'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($punyaLink)
                <div class="card h-100">
                    <div class="card-header">
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small">Pratinjau Link Aktif</div>
                            <h3 class="card-title mt-1">Link yang sedang ditampilkan untuk pengguna ini</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($linkAktif->isEmpty())
                            <div class="empty">
                                <div class="empty-img">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-link-off" width="64" height="64" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 15l3 -3m2 -2l1 -1a3 3 0 0 1 4 4l-1 1" />
                                        <path d="M5 11l-1 1a3 3 0 0 0 4 4l1 -1" />
                                        <path d="M3 3l18 18" />
                                    </svg>
                                </div>
                                <p class="empty-title">Belum ada link aktif</p>
                                <p class="empty-subtitle text-secondary">
                                    Anda sudah punya data link, tetapi belum ada yang diaktifkan untuk halaman publik.
                                </p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($linkAktif as $link)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                                            <div class="min-w-0">
                                                <div class="fw-semibold">{{ $link->judul }}</div>
                                                @if ($link->deskripsi)
                                                    <div class="text-secondary small mt-1">{{ $link->deskripsi }}</div>
                                                @endif
                                                <div class="text-secondary small mt-2 text-truncate">{{ $link->urlTujuan() }}</div>
                                                <div class="small text-primary mt-2">{{ number_format($link->total_klik) }} klik</div>
                                            </div>
                                            <div class="d-flex gap-2 align-items-center">
                                                <span class="badge bg-success-lt text-success">Urutan {{ $link->urutan }}</span>
                                                <a href="{{ $link->url_publik }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                                    Buka
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-copy-text="{{ $link->urlTujuan() }}">
                                                    Salin
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Daftar Link</div>
                        <h3 class="card-title mt-1">Kelola link yang tampil pada modul ini</h3>
                    </div>
                </div>
                <div class="card-body">
                    @if (! $punyaLink)
                        <div class="empty py-5">
                            <p class="empty-title">Belum ada link</p>
                            <p class="empty-subtitle text-secondary">
                                Gunakan form di atas untuk menambahkan link edukasi, promo, marketplace, katalog, atau booking.
                            </p>
                        </div>
                    @else
                        <div class="d-grid gap-4">
                            @foreach ($items as $link)
                                <div class="border rounded-3 p-3 p-md-4" id="editor-link-{{ $link->id }}" data-link-editor>
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                        <div>
                                            <div class="fw-semibold">{{ $link->judul }}</div>
                                            <div class="text-secondary small">{{ $link->urlTujuan() }}</div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge {{ $link->aktif ? 'bg-success-lt text-success' : 'bg-warning-lt text-warning' }}">
                                                {{ $link->aktif ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            <span class="badge bg-primary-lt text-primary">{{ number_format($link->total_klik) }} klik</span>
                                            <a href="#editor-link-{{ $link->id }}" data-link-editor-trigger class="btn btn-outline-primary btn-sm">
                                                Edit
                                            </a>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('tools.link.update', $link) }}" class="d-grid gap-3">
                                        @csrf
                                        @method('PUT')

                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label class="form-label">Judul</label>
                                                <input type="text" name="judul" value="{{ old('judul', $link->judul) }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Urutan</label>
                                                <input type="number" min="0" max="999" name="urutan" value="{{ old('urutan', $link->urutan) }}" class="form-control">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form-label">Deskripsi</label>
                                            <input type="text" name="deskripsi" value="{{ old('deskripsi', $link->deskripsi) }}" class="form-control">
                                        </div>

                                        <div>
                                            <label class="form-label">URL</label>
                                            <input
                                                type="text"
                                                name="url"
                                                value="{{ old('url', $link->url) }}"
                                                class="form-control"
                                                placeholder="https://example.com atau example.com"
                                                data-link-url-input
                                                required
                                            >
                                            <div class="form-hint">Gunakan link website lengkap atau cukup `example.com`.</div>
                                        </div>

                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                            <label class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="aktif" value="1" @checked($link->aktif)>
                                                <span class="form-check-label">Tampilkan pada modul link</span>
                                            </label>

                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">Simpan Link</button>
                                            </div>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('tools.link.destroy', $link) }}" class="mt-3 d-flex justify-content-end">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus Link</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
