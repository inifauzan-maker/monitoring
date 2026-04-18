@extends('tataletak.aplikasi')

@section('judul_halaman', 'Siswa')
@section('pratitel_halaman', 'Modul')
@section('judul_konten', 'Siswa')
@section('deskripsi_halaman', 'Pusat data siswa, validasi, tagihan, dan status pembayaran yang terhubung ke produk.')

@section('aksi_halaman')
    @if ($isSuperadmin)
        <a href="#form-siswa" class="btn btn-primary">{{ $editItem ? 'Ubah Data Siswa' : 'Tambah Data Siswa' }}</a>
    @endif
@endsection

@section('konten')
    @php
        $totalSiswa = $ringkasan->count();
        $totalTervalidasi = $ringkasan->where('status_validasi', 'validated')->count();
        $totalInvoice = (int) $ringkasan->sum('total_invoice');
        $totalPembayaran = (int) $ringkasan->sum('jumlah_pembayaran');
        $parameterFilter = [];

        if ($selectedProgram) {
            $parameterFilter['program'] = $selectedProgram;
        }

        if ($selectedStatusValidasi) {
            $parameterFilter['status_validasi'] = $selectedStatusValidasi;
        }

        if ($selectedStatusPembayaran) {
            $parameterFilter['status_pembayaran'] = $selectedStatusPembayaran;
        }

        if ($selectedLokasi) {
            $parameterFilter['lokasi'] = $selectedLokasi;
        }

        if ($selectedKode) {
            $parameterFilter['kode'] = $selectedKode;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Siswa</div><div class="h1 mb-0 mt-2">{{ number_format($totalSiswa, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Tervalidasi</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($totalTervalidasi, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Tagihan</div><div class="h3 mb-0 mt-2">Rp {{ number_format($totalInvoice, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Pembayaran</div><div class="h3 mb-0 mt-2">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Siswa</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" for="filter_program">Program</label>
                            <select class="form-select" id="filter_program" name="program">
                                <option value="">Semua Program</option>
                                @foreach ($programs as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedProgram === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="filter_status_validasi">Validasi</label>
                            <select class="form-select" id="filter_status_validasi" name="status_validasi">
                                <option value="">Semua</option>
                                @foreach ($statusValidasiOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatusValidasi === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="filter_status_pembayaran">Pembayaran</label>
                            <select class="form-select" id="filter_status_pembayaran" name="status_pembayaran">
                                <option value="">Semua</option>
                                @foreach ($statusPembayaranOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatusPembayaran === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="filter_lokasi">Lokasi</label>
                            <select class="form-select" id="filter_lokasi" name="lokasi">
                                <option value="">Semua Lokasi</option>
                                @foreach ($lokasiOptions as $lokasi)
                                    <option value="{{ $lokasi }}" @selected($selectedLokasi === $lokasi)>{{ $lokasi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="filter_kode">Kode</label>
                            <select class="form-select" id="filter_kode" name="kode">
                                <option value="">Semua Kode</option>
                                @foreach ($kodeOptions as $kode)
                                    <option value="{{ $kode }}" @selected($selectedKode === $kode)>{{ $kode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($parameterFilter)
                                    <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-siswa">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">{{ $editItem ? 'Ubah Data Siswa' : 'Tambah Data Siswa' }}</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('siswa.update', $editItem) : route('siswa.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            @foreach ($parameterFilter as $key => $value)
                                <input type="hidden" name="filter_{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="produk_item_id">Produk</label>
                                    <select class="form-select" id="produk_item_id" name="produk_item_id" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach ($produkOptions as $produk)
                                            <option value="{{ $produk->id }}" @selected((string) old('produk_item_id', $editItem?->produk_item_id) === (string) $produk->id)>{{ $produk->labelProgram() }} - {{ $produk->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $editItem?->nama_lengkap) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="asal_sekolah">Asal Sekolah</label>
                                    <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" value="{{ old('asal_sekolah', $editItem?->asal_sekolah) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="tingkat_kelas">Tingkat Kelas</label>
                                    <input type="text" class="form-control" id="tingkat_kelas" name="tingkat_kelas" value="{{ old('tingkat_kelas', $editItem?->tingkat_kelas) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="jurusan">Jurusan</label>
                                    <input type="text" class="form-control" id="jurusan" name="jurusan" value="{{ old('jurusan', $editItem?->jurusan) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="nomor_telepon">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $editItem?->nomor_telepon) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="nama_orang_tua">Nama Orang Tua</label>
                                    <input type="text" class="form-control" id="nama_orang_tua" name="nama_orang_tua" value="{{ old('nama_orang_tua', $editItem?->nama_orang_tua) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="nomor_telepon_orang_tua">Telepon Orang Tua</label>
                                    <input type="text" class="form-control" id="nomor_telepon_orang_tua" name="nomor_telepon_orang_tua" value="{{ old('nomor_telepon_orang_tua', $editItem?->nomor_telepon_orang_tua) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="lokasi_belajar">Lokasi Belajar</label>
                                    <input type="text" class="form-control" id="lokasi_belajar" name="lokasi_belajar" value="{{ old('lokasi_belajar', $editItem?->lokasi_belajar) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="provinsi">Provinsi</label>
                                    <input type="text" class="form-control" id="provinsi" name="provinsi" value="{{ old('provinsi', $editItem?->provinsi) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="kota">Kota</label>
                                    <input type="text" class="form-control" id="kota" name="kota" value="{{ old('kota', $editItem?->kota) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="sistem_pembayaran">Sistem Pembayaran</label>
                                    <select class="form-select" id="sistem_pembayaran" name="sistem_pembayaran" required>
                                        @foreach ($sistemPembayaranOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(old('sistem_pembayaran', $editItem?->sistem_pembayaran ?? 'lunas') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="status_validasi">Status Validasi</label>
                                    <select class="form-select" id="status_validasi" name="status_validasi" required>
                                        @foreach ($statusValidasiOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(old('status_validasi', $editItem?->status_validasi ?? 'pending') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="total_invoice">Total Tagihan</label>
                                    <input type="number" class="form-control" id="total_invoice" name="total_invoice" value="{{ old('total_invoice', $editItem?->total_invoice) }}" min="0" placeholder="Kosongkan untuk hitung otomatis">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="jumlah_pembayaran">Jumlah Pembayaran</label>
                                    <input type="number" class="form-control" id="jumlah_pembayaran" name="jumlah_pembayaran" value="{{ old('jumlah_pembayaran', $editItem?->jumlah_pembayaran ?? 0) }}" min="0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="keterangan">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $editItem?->keterangan) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">{{ $editItem ? 'Simpan Perubahan' : 'Simpan Data Siswa' }}</button>
                                @if ($editItem)
                                    <a href="{{ route('siswa.index', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Siswa</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Program</th>
                                <th>Lokasi</th>
                                <th>Kontak</th>
                                <th>Validasi</th>
                                <th>Tagihan</th>
                                <th>Pembayaran</th>
                                @if ($isSuperadmin)
                                    <th class="w-1">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                                    <td><div class="fw-semibold">{{ $item->nama_lengkap }}</div><div class="text-secondary small">{{ $item->asal_sekolah ?: '-' }}</div></td>
                                    <td><div>{{ $programs[$item->program] ?? $item->program ?? '-' }}</div><div class="text-secondary small">{{ $item->nama_program ?: '-' }}</div></td>
                                    <td><div>{{ $item->lokasi_belajar ?: '-' }}</div><div class="text-secondary small">{{ trim(($item->kota ?: '').' / '.($item->provinsi ?: ''), ' /') ?: '-' }}</div></td>
                                    <td><div>{{ $item->nomor_telepon ?: '-' }}</div><div class="text-secondary small">{{ $item->nomor_telepon_orang_tua ?: '-' }}</div></td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusValidasi() }}">{{ $item->labelStatusValidasi() }}</span><div class="text-secondary small mt-1">{{ $item->tanggal_validasi?->format('d M Y H:i') ?: 'Belum divalidasi' }}</div></td>
                                    <td><div class="fw-semibold">{{ $item->nomor_invoice ?: '-' }}</div><div class="text-secondary small">Rp {{ number_format((int) $item->total_invoice, 0, ',', '.') }}</div></td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusPembayaran() }}">{{ $item->labelStatusPembayaran() }}</span><div class="text-secondary small mt-1">Bayar: Rp {{ number_format((int) $item->jumlah_pembayaran, 0, ',', '.') }}</div><div class="text-secondary small">Sisa: Rp {{ number_format((int) $item->sisa_tagihan, 0, ',', '.') }}</div></td>
                                    @if ($isSuperadmin)
                                        <td>
                                            @php($parameterEdit = ['siswa' => $item] + $parameterFilter)
                                            <div class="btn-list justify-content-end flex-nowrap">
                                                <a href="{{ route('siswa.edit', $parameterEdit) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <form method="POST" action="{{ route('siswa.destroy', $item) }}" onsubmit="return confirm('Hapus data siswa ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    @foreach ($parameterFilter as $key => $value)
                                                        <input type="hidden" name="filter_{{ $key }}" value="{{ $value }}">
                                                    @endforeach
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperadmin ? 9 : 8 }}" class="text-center text-secondary">Belum ada data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $items->links() }}</div>
            </div>
        </div>
    </div>
@endsection
