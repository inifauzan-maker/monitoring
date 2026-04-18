@extends('tataletak.autentikasi')

@section('judul_halaman', 'Masuk')

@section('konten')
    <div class="text-center mb-4">
        <a href="{{ route('login') }}" class="navbar-brand navbar-brand-autodark d-inline-flex align-items-center gap-3">
            <span class="avatar avatar-rounded bg-primary text-primary-fg">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 12h5m6 0h5" />
                    <path d="M12 4v5m0 6v5" />
                    <path d="M7.5 7.5l3.5 3.5" />
                    <path d="M13 13l3.5 3.5" />
                    <path d="M16.5 7.5l-3.5 3.5" />
                    <path d="M11 13l-3.5 3.5" />
                </svg>
            </span>
            <span class="fs-2 fw-bold text-body">{{ config('app.name', 'Monitoring') }}</span>
        </a>
    </div>

    <div class="card card-md auth-panel">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Masuk ke dashboard</h2>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('sesi.store') }}" method="POST" autocomplete="off">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-2">
                    <label class="form-label" for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" name="ingat_saya" value="1" {{ old('ingat_saya') ? 'checked' : '' }}>
                        <span class="form-check-label">Ingat saya</span>
                    </label>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-secondary mt-3 small">
        Gunakan akun yang sudah terdaftar melalui seeder atau pengaturan pengguna.
    </div>
@endsection
