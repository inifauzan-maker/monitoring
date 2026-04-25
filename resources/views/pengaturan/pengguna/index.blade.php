@extends('tataletak.aplikasi')

@section('judul_halaman', 'Pengaturan Pengguna')
@section('pratitel_halaman', 'Pengaturan')
@section('judul_konten', 'Pengaturan Pengguna')
@section('deskripsi_halaman', 'Kelola akun aplikasi dan tetapkan level akses dari superadmin, admin, owner/direksi, PIC, leader, hingga staff.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('pengaturan.pengguna.create') }}" class="btn btn-primary">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
            </svg>
            Tambah Pengguna
        </a>
    </div>
@endsection

@section('konten')
    @php($penggunaMasuk = auth()->user())

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Level Akses</th>
                        <th>Diperbarui</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penggunas as $pengguna)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $pengguna->name }}</div>
                                @if ($penggunaMasuk?->is($pengguna))
                                    <div class="small text-secondary">Akun yang sedang dipakai</div>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $pengguna->email }}</td>
                            <td>
                                <span class="badge {{ $pengguna->kelasBadgeLevelAkses() }}">
                                    {{ $pengguna->labelLevelAkses() }}
                                </span>
                            </td>
                            <td class="text-secondary">{{ $pengguna->updated_at?->format('d M Y H:i') }}</td>
                            <td>
                                <div class="btn-list justify-content-end flex-nowrap">
                                    <a href="{{ route('pengaturan.pengguna.edit', $pengguna) }}" class="btn btn-outline-primary btn-sm">
                                        Ubah
                                    </a>

                                    <form action="{{ route('pengaturan.pengguna.destroy', $pengguna) }}" method="POST"
                                        onsubmit="return confirm('Hapus pengguna {{ $pengguna->name }}?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                            {{ $penggunaMasuk?->is($pengguna) ? 'disabled' : '' }}>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty py-5">
                                    <p class="empty-title">Belum ada data pengguna</p>
                                    <p class="empty-subtitle text-secondary">
                                        Tambahkan akun pertama agar pengaturan RBAC bisa mulai digunakan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
