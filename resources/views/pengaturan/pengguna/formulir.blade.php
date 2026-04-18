@extends('tataletak.aplikasi')

@section('judul_halaman', $judulForm)
@section('pratitel_halaman', 'Pengaturan')
@section('judul_konten', $judulForm)
@section('deskripsi_halaman', $keteranganForm)

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('pengaturan.pengguna.index') }}" class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>
@endsection

@section('konten')
    @php($apakahUbah = $pengguna->exists)
    @php($levelAktif = old('level_akses', $pengguna->level_akses?->value ?? 'level_5'))

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="{{ $aksiForm }}" method="POST" class="card">
                @csrf

                @if ($metodeForm !== 'POST')
                    @method($metodeForm)
                @endif

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Nama</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $pengguna->name) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $pengguna->email) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="level_akses">Level akses</label>
                            <select
                                id="level_akses"
                                name="level_akses"
                                class="form-select @error('level_akses') is-invalid @enderror"
                                required
                            >
                                @foreach ($opsiLevelAkses as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($levelAktif === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="alert alert-secondary mb-0 w-100">
                                Superadmin memiliki akses penuh. Level 1 sampai 5 bisa dipakai untuk pembatasan modul berikutnya.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="password">
                                Password
                                @if ($apakahUbah)
                                    <span class="text-secondary">(biarkan kosong jika tidak diubah)</span>
                                @endif
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                {{ $apakahUbah ? '' : 'required' }}
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                {{ $apakahUbah ? '' : 'required' }}
                            >
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        {{ $labelTombol }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
