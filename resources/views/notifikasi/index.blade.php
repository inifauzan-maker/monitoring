@extends('tataletak.aplikasi')

@section('pratitel_halaman', 'Aktivitas Sistem')
@section('judul_konten', 'Notifikasi')
@section('deskripsi_halaman', 'Pantau pembaruan tugas, artikel, akun, dan aktivitas penting lain yang perlu Anda tindak lanjuti.')

@section('aksi_halaman')
    <form action="{{ route('notifikasi.baca_semua') }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        <input type="hidden" name="redirect_ke" value="{{ request()->fullUrl() }}">
        <button type="submit" class="btn btn-outline-primary">
            Tandai Semua Sudah Dibaca
        </button>
    </form>
@endsection

@section('konten')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card kartu-ringkasan h-100">
                <div class="card-body">
                    <div class="text-secondary text-uppercase small fw-semibold">Total Notifikasi</div>
                    <div class="display-6 fw-bold mt-2">{{ $jumlahSemua }}</div>
                    <div class="text-secondary small mt-2">Semua notifikasi yang tercatat untuk akun Anda.</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card kartu-ringkasan h-100">
                <div class="card-body">
                    <div class="text-secondary text-uppercase small fw-semibold">Belum Dibaca</div>
                    <div class="display-6 fw-bold mt-2">{{ $jumlahBelumDibaca }}</div>
                    <div class="text-secondary small mt-2">Segera periksa item yang masih butuh perhatian.</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card kartu-ringkasan h-100">
                <div class="card-body">
                    <div class="text-secondary text-uppercase small fw-semibold">Masuk Hari Ini</div>
                    <div class="display-6 fw-bold mt-2">{{ $jumlahHariIni }}</div>
                    <div class="text-secondary small mt-2">Aktivitas baru yang tercatat sepanjang hari ini.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Filter Notifikasi</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('notifikasi.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label">Status Baca</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($opsiStatus as $value => $label)
                            <option value="{{ $value }}" @selected($statusDipilih === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tipe" class="form-label">Tipe</label>
                    <select id="tipe" name="tipe" class="form-select">
                        <option value="">Semua Tipe</option>
                        @foreach ($opsiTipe as $value => $label)
                            <option value="{{ $value }}" @selected($tipeDipilih === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('notifikasi.index') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Notifikasi</h3>
        </div>

        @if ($items->isEmpty())
            <div class="card-body">
                <div class="empty">
                    <div class="empty-img">
                        @include('komponen.ikon_notifikasi', ['tipe' => 'info', 'kelas' => 'text-secondary'])
                    </div>
                    <p class="empty-title">Belum ada notifikasi</p>
                    <p class="empty-subtitle text-secondary">
                        Notifikasi baru akan muncul di sini saat ada penugasan, pembaruan artikel, atau aktivitas penting lain.
                    </p>
                </div>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach ($items as $notifikasi)
                    <div class="list-group-item notifikasi-item {{ $notifikasi->sudahDibaca() ? '' : 'notifikasi-item-belum' }}">
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0 pt-1">
                                @include('komponen.ikon_notifikasi', [
                                    'tipe' => $notifikasi->tipe,
                                    'kelas' => $notifikasi->kelasIkonTipe(),
                                ])
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <div class="fw-semibold">{{ $notifikasi->judul }}</div>
                                            <span class="badge {{ $notifikasi->kelasBadgeTipe() }}">{{ $notifikasi->labelTipe() }}</span>
                                            @unless ($notifikasi->sudahDibaca())
                                                <span class="badge bg-primary-lt text-primary">Baru</span>
                                            @endunless
                                        </div>
                                        <div class="text-secondary mt-2">{{ $notifikasi->pesan }}</div>
                                    </div>
                                    <div class="text-secondary small text-md-end">
                                        {{ $notifikasi->created_at?->diffForHumans() }}
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                    @unless ($notifikasi->sudahDibaca())
                                        <form action="{{ route('notifikasi.baca', $notifikasi) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="redirect_ke" value="{{ $notifikasi->tautan ?: request()->fullUrl() }}">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                {{ $notifikasi->tautan ? 'Buka Notifikasi' : 'Tandai Sudah Dibaca' }}
                                            </button>
                                        </form>
                                    @endunless

                                    @if ($notifikasi->tautan)
                                        <a href="{{ $notifikasi->tautan }}" class="btn btn-sm btn-outline-secondary">
                                            {{ $notifikasi->sudahDibaca() ? 'Buka Tautan' : 'Lihat Tanpa Ubah Status' }}
                                        </a>
                                    @endif

                                    <span class="text-secondary small">
                                        {{ $notifikasi->sudahDibaca() ? 'Sudah dibaca' : 'Belum dibaca' }}
                                        @if ($notifikasi->dibaca_pada)
                                            pada {{ $notifikasi->dibaca_pada->translatedFormat('d M Y H:i') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card-footer">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection
