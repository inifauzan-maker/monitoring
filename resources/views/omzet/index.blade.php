@extends('tataletak.aplikasi')

@section('judul_halaman', 'Omzet')
@section('pratitel_halaman', 'Modul')
@section('judul_konten', 'Omzet')
@section('deskripsi_halaman', 'Rekap tagihan, pembayaran, dan sisa tagihan dari data siswa tervalidasi.')

@section('konten')
    @php
        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Tagihan</div><div class="h3 mb-0 mt-2">Rp {{ number_format($totalInvoice, 0, ',', '.') }}</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Pembayaran</div><div class="h3 mb-0 mt-2 text-green">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Sisa Tagihan</div><div class="h3 mb-0 mt-2 text-orange">Rp {{ number_format($totalSisaTagihan, 0, ',', '.') }}</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Siswa Lunas</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahSiswaLunas, 0, ',', '.') }}</div></div></div></div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Omzet</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" for="tahun">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun">
                                @foreach ($yearOptions as $tahun)
                                    <option value="{{ $tahun }}" @selected($selectedTahun === $tahun)>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="bulan">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <option value="0" @selected($selectedBulan === 0)>Semua Bulan</option>
                                @foreach ($bulanOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedBulan === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="program">Program</label>
                            <select class="form-select" id="program" name="program">
                                <option value="">Semua Program</option>
                                @foreach ($programs as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedProgram === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="status_pembayaran">Pembayaran</label>
                            <select class="form-select" id="status_pembayaran" name="status_pembayaran">
                                <option value="">Semua Status</option>
                                @foreach ($statusPembayaranOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatusPembayaran === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                <a href="{{ route('omzet.index') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Rekap per Program</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr><th>Program</th><th>Siswa</th><th>Tagihan</th><th>Bayar</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($perProgram as $item)
                                <tr>
                                    <td>{{ $item['label'] }}</td>
                                    <td>{{ number_format($item['jumlah_siswa'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item['total_invoice'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item['jumlah_pembayaran'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary">Belum ada rekap omzet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Transaksi Siswa</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr><th>Tanggal</th><th>Nama</th><th>Program</th><th>Tagihan</th><th>Pembayaran</th><th>Sisa</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksi as $item)
                                <tr>
                                    <td>{{ $item->created_at?->format('d M Y') }}</td>
                                    <td>{{ $item->nama_lengkap }}</td>
                                    <td><div>{{ $programs[$item->program] ?? $item->program ?? '-' }}</div><div class="text-secondary small">{{ $item->nama_program ?: '-' }}</div></td>
                                    <td><div class="fw-semibold">{{ $item->nomor_invoice ?: '-' }}</div><div class="text-secondary small">Rp {{ number_format((int) $item->total_invoice, 0, ',', '.') }}</div></td>
                                    <td>Rp {{ number_format((int) $item->jumlah_pembayaran, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format((int) $item->sisa_tagihan, 0, ',', '.') }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeStatusPembayaran() }}">{{ $item->labelStatusPembayaran() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-secondary">Belum ada data omzet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $transaksi->links() }}</div>
            </div>
        </div>
    </div>
@endsection
