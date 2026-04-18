@extends('tataletak.aplikasi')

@section('judul_halaman', 'Dashboard Editorial')
@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Dashboard Editorial Artikel')
@section('deskripsi_halaman', 'Ringkasan alur editorial artikel: draft, artikel siap terbit, jadwal terbit, dan artikel yang perlu ditindaklanjuti.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.editorial.pdf', request()->query()) }}" class="btn btn-outline-secondary">Export PDF</a>
        <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-secondary">Artikel Saya</a>
        <a href="{{ route('tools.artikel.create') }}" class="btn btn-primary">Buat Artikel</a>
    </div>
@endsection

@section('konten')
    <div class="row row-cards mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Editorial</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="penulis">Penulis</label>
                            <select class="form-select" id="penulis" name="penulis">
                                <option value="">Semua Penulis</option>
                                @foreach ($daftarPenulis as $itemPenulis)
                                    <option value="{{ $itemPenulis->id }}" @selected((int) $penulisDipilih?->id === (int) $itemPenulis->id)>
                                        {{ $itemPenulis->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="kategori">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                @foreach ($daftarKategori as $itemKategori)
                                    <option value="{{ $itemKategori->id }}" @selected((int) $kategoriDipilih?->id === (int) $itemKategori->id)>
                                        {{ $itemKategori->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="status_editorial">Status</label>
                            <select class="form-select" id="status_editorial" name="status_editorial">
                                <option value="">Semua Status</option>
                                @foreach ($opsiStatusEditorial as $nilaiStatus => $labelStatus)
                                    <option value="{{ $nilaiStatus }}" @selected($statusEditorialDipilih === $nilaiStatus)>
                                        {{ $labelStatus }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="basis_tanggal">Basis Tanggal</label>
                            <select class="form-select" id="basis_tanggal" name="basis_tanggal">
                                @foreach ($opsiBasisTanggalEditorial as $nilaiBasis => $labelBasis)
                                    <option value="{{ $nilaiBasis }}" @selected($basisTanggalDipilih === $nilaiBasis)>
                                        {{ $labelBasis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="tanggal_dari">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ $tanggalDariFilter }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="tanggal_sampai">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ $tanggalSampaiFilter }}">
                        </div>
                        <div class="col-md-3">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($filterAktif)
                                    <a href="{{ route('tools.artikel.editorial') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <div class="text-secondary text-uppercase fw-semibold small mb-2">Preset Cepat</div>
                    <div class="btn-list">
                        @foreach ($opsiPresetEditorial as $nilaiPreset => $preset)
                            <a href="{{ route('tools.artikel.editorial', $preset['query']) }}"
                                class="btn btn-sm {{ $presetEditorialDipilih === $nilaiPreset ? 'btn-primary' : 'btn-outline-secondary' }}"
                                title="{{ $preset['deskripsi'] }}">
                                {{ $preset['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-6">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Simpan Preset Kustom Saya</div>
                            @if ($dapatSimpanPresetKustom)
                                <form method="POST" action="{{ route('tools.artikel.editorial.preset.store') }}" class="row g-2">
                                    @csrf
                                    <input type="hidden" name="penulis" value="{{ $penulisDipilih?->id }}">
                                    <input type="hidden" name="kategori" value="{{ $kategoriDipilih?->id }}">
                                    <input type="hidden" name="status_editorial" value="{{ $statusEditorialDipilih }}">
                                    <input type="hidden" name="basis_tanggal" value="{{ $basisTanggalDipilih }}">
                                    <input type="hidden" name="tanggal_dari" value="{{ $tanggalDariFilter }}">
                                    <input type="hidden" name="tanggal_sampai" value="{{ $tanggalSampaiFilter }}">
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama_preset" placeholder="Nama preset kustom" required maxlength="100">
                                    </div>
                                    <div class="col-sm-4">
                                        <button type="submit" class="btn btn-primary w-100">Simpan Preset</button>
                                    </div>
                                </form>
                            @else
                                <div class="text-secondary small">Aktifkan minimal satu filter untuk menyimpan preset kustom.</div>
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Preset Kustom Pengguna</div>
                            <div class="d-grid gap-2">
                                @forelse ($presetEditorialPengguna as $presetPengguna)
                                    <div class="border rounded p-2">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $presetPengguna->nama_preset }}</div>
                                                <div class="text-secondary small">
                                                    {{ collect($presetPengguna->konfigurasi_filter ?? [])->keys()->map(fn ($item) => str($item)->replace('_', ' ')->title())->join(', ') ?: 'Tanpa filter' }}
                                                </div>
                                            </div>
                                            <div class="btn-list">
                                                <a href="{{ route('tools.artikel.editorial', array_merge($presetPengguna->konfigurasi_filter ?? [], ['preset_pengguna' => $presetPengguna->id])) }}"
                                                    class="btn btn-sm {{ (int) $presetPenggunaDipilih?->id === (int) $presetPengguna->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                                                    Pakai
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('tools.artikel.editorial.preset.destroy', array_merge(['presetEditorialPengguna' => $presetPengguna->id], request()->query())) }}"
                                                    onsubmit="return confirm('Hapus preset kustom ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-secondary small">Belum ada preset kustom tersimpan untuk akun ini.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @if ($filterAktif)
                    <div class="card-footer text-secondary small">
                        Filter aktif:
                        @if ($presetEditorialDipilih)
                            <span class="badge bg-dark-lt text-dark">Preset: {{ $opsiPresetEditorial[$presetEditorialDipilih]['label'] }}</span>
                        @endif
                        @if ($presetPenggunaDipilih)
                            <span class="badge bg-indigo-lt text-indigo">Preset Kustom: {{ $presetPenggunaDipilih->nama_preset }}</span>
                        @endif
                        @if ($penulisDipilih)
                            <span class="badge bg-primary-lt text-primary">Penulis: {{ $penulisDipilih->name }}</span>
                        @endif
                        @if ($kategoriDipilih)
                            <span class="badge bg-secondary-lt text-secondary">Kategori: {{ $kategoriDipilih->nama }}</span>
                        @endif
                        @if ($statusEditorialDipilih)
                            <span class="badge bg-orange-lt text-orange">Status: {{ $opsiStatusEditorial[$statusEditorialDipilih] }}</span>
                        @endif
                        @if ($filterTanggalAktif)
                            <span class="badge bg-blue-lt text-blue">{{ $labelFilterTanggal }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Artikel</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahSemua, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Draft</div><div class="h1 mb-0 mt-2 text-orange">{{ number_format($jumlahDraft, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Terjadwal</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($jumlahTerjadwal, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Terbit Aktif</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($jumlahTerbitAktif, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Siap Terbit</div><div class="h1 mb-0 mt-2 text-primary">{{ number_format($jumlahSiapTerbit, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Perlu Revisi</div><div class="h1 mb-0 mt-2 text-red">{{ number_format($jumlahPerluRevisi, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notifikasi Editorial</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($notifikasiEditorial as $notifikasi)
                            <div class="col-md-6 col-xl-3">
                                <div class="alert alert-{{ $notifikasi['tipe'] === 'danger' ? 'danger' : ($notifikasi['tipe'] === 'warning' ? 'warning' : ($notifikasi['tipe'] === 'success' ? 'success' : 'info')) }} mb-0 h-100">
                                    <div class="fw-semibold mb-1">{{ $notifikasi['judul'] }}</div>
                                    <div class="small">{{ $notifikasi['pesan'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Jadwal Terbit Terdekat</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($artikelTerjadwal as $item)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->judul }}</div>
                                    <div class="text-secondary small">{{ $item->kategori?->nama ?: 'Tanpa kategori' }} • {{ $item->penulis?->name ?: 'Penulis belum diatur' }}</div>
                                    <div class="text-secondary small">{{ $item->diterbitkan_pada?->format('d M Y H:i') ?: '-' }}</div>
                                </div>
                                <a href="{{ route('tools.artikel.edit', $item) }}" class="btn btn-outline-primary btn-sm">Kelola</a>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada artikel terjadwal.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Siap Terbit</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($artikelSiapTerbit as $item)
                        @php($evaluasi = $item->evaluasiKesiapan())
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->judul }}</div>
                                    <div class="text-secondary small">{{ $item->kategori?->nama ?: 'Tanpa kategori' }} • Skor {{ $evaluasi['skor'] }}/100</div>
                                    <div class="text-secondary small">Diperbarui {{ $item->updated_at?->format('d M Y H:i') ?: '-' }}</div>
                                </div>
                                <a href="{{ route('tools.artikel.edit', $item) }}" class="btn btn-outline-success btn-sm">Terbitkan</a>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada draft yang siap terbit.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Perlu Tindak Lanjut</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($artikelPerluRevisi as $item)
                        @php($evaluasi = $item->evaluasiKesiapan())
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->judul }}</div>
                                    <div class="text-secondary small">{{ $item->kategori?->nama ?: 'Tanpa kategori' }} • {{ $evaluasi['label'] }}</div>
                                    <div class="text-secondary small">Skor {{ $evaluasi['skor'] }}/100 • {{ $item->updated_at?->format('d M Y H:i') ?: '-' }}</div>
                                </div>
                                <a href="{{ route('tools.artikel.edit', $item) }}" class="btn btn-outline-warning btn-sm">Perbaiki</a>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Semua artikel saat ini sudah siap.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Artikel Terbit Terbaru</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Terbit</th>
                                <th>Revisi</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($artikelTerbitTerbaru as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul }}</div>
                                        <div class="text-secondary small">{{ $item->kategori?->nama ?: 'Tanpa kategori' }}</div>
                                    </td>
                                    <td>{{ $item->penulis?->name ?: '-' }}</td>
                                    <td>{{ $item->diterbitkan_pada?->format('d M Y H:i') ?: '-' }}</td>
                                    <td>{{ number_format((int) $item->revisi_count, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="btn-list justify-content-end flex-nowrap">
                                            <a href="{{ route('tools.artikel.show', $item) }}" class="btn btn-outline-primary btn-sm">Buka</a>
                                            <a href="{{ route('tools.artikel.pdf', $item) }}" class="btn btn-outline-secondary btn-sm">PDF</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">Belum ada artikel yang sudah tayang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ $filterAktif ? 'Artikel Hasil Filter Terbaru' : 'Artikel Terkini' }}</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($artikelFilterTerbaru as $item)
                        <a href="{{ route('tools.artikel.edit', $item) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->judul }}</div>
                                    <div class="text-secondary small">
                                        {{ $item->kategori?->nama ?: 'Tanpa kategori' }} • {{ $item->penulis?->name ?: 'Penulis belum diatur' }}
                                    </div>
                                </div>
                                <span class="badge {{ $item->kelasBadgePublikasi() }}">{{ $item->labelStatusPublikasi() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada artikel yang cocok dengan filter editorial ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
