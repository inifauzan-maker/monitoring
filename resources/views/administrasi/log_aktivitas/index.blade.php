@extends('tataletak.aplikasi')

@section('judul_halaman', 'Log Aktivitas')
@section('pratitel_halaman', 'Administrasi')
@section('judul_konten', 'Log Aktivitas')
@section('deskripsi_halaman', 'Pusat pencatatan aktivitas sistem, perubahan data penting, dan jejak akses pengguna.')

@section('konten')
    @php
        $filterAktif = filled($selectedModul) || filled($selectedAksi) || filled($selectedPengguna) || filled($tanggalDari) || filled($tanggalSampai) || filled($kataKunci);
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Log</div><div class="h1 mb-0 mt-2">{{ number_format($ringkasan['total'], 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Hari Ini</div><div class="h1 mb-0 mt-2 text-blue">{{ number_format($ringkasan['hari_ini'], 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Autentikasi</div><div class="h1 mb-0 mt-2 text-green">{{ number_format($ringkasan['autentikasi'], 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Perubahan Data</div><div class="h1 mb-0 mt-2 text-yellow">{{ number_format($ringkasan['perubahan_data'], 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Log Aktivitas</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" for="modul">Modul</label>
                            <select class="form-select" id="modul" name="modul">
                                <option value="">Semua Modul</option>
                                @foreach ($opsiModul as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedModul === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="aksi">Aksi</label>
                            <select class="form-select" id="aksi" name="aksi">
                                <option value="">Semua Aksi</option>
                                @foreach ($opsiAksi as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedAksi === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="user_id">Pengguna</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Semua Pengguna</option>
                                @foreach ($opsiPengguna as $pengguna)
                                    <option value="{{ $pengguna->id }}" @selected($selectedPengguna === $pengguna->id)>{{ $pengguna->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="q">Cari</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $kataKunci }}" placeholder="Deskripsi, IP, subjek, pengguna">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="tanggal_dari">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ $tanggalDari }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="tanggal_sampai">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ $tanggalSampai }}">
                        </div>
                        <div class="col-md-6">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($filterAktif)
                                    <a href="{{ route('administrasi.log_aktivitas.index') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Aktivitas</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Modul</th>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                                <th>Subjek</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div>{{ $item->created_at?->format('d M Y H:i') ?: '-' }}</div>
                                        <div class="text-secondary small">{{ $item->created_at?->diffForHumans() ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->pengguna?->name ?: 'Sistem / Tamu' }}</div>
                                        <div class="text-secondary small">{{ $item->pengguna?->email ?: '-' }}</div>
                                    </td>
                                    <td>{{ $item->labelModul() }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeAksi() }}">{{ $item->labelAksi() }}</span></td>
                                    <td style="min-width: 24rem;">
                                        <div>{{ $item->deskripsi }}</div>
                                        @if (! empty($item->metadata))
                                            <div class="text-secondary small mt-1">
                                                @foreach ($item->metadata as $key => $value)
                                                    <div>
                                                        <span class="fw-semibold">{{ \Illuminate\Support\Str::headline((string) $key) }}:</span>
                                                        @if (is_array($value))
                                                            {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $item->labelSubjek() }}</td>
                                    <td>
                                        <div>{{ $item->ip_address ?: '-' }}</div>
                                        <div class="text-secondary small">{{ \Illuminate\Support\Str::limit((string) $item->user_agent, 48) ?: '-' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary">Belum ada log aktivitas yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($items->hasPages())
                    <div class="card-footer">{{ $items->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
