@extends('tataletak.aplikasi')

@section('judul_halaman', 'Kategori Artikel')
@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Kategori Artikel')
@section('deskripsi_halaman', 'Kelola kategori artikel yang dipakai untuk pengelompokan konten.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-secondary">Artikel Saya</a>
        <a href="{{ route('tools.artikel.create') }}" class="btn btn-primary">Buat Artikel</a>
    </div>
@endsection

@section('konten')
    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Kategori</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tools.artikel.kategori.store') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="nama">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Kosongkan untuk otomatis">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Kategori</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Slug</th>
                                <th>Jumlah Artikel</th>
                                <th>Deskripsi</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->nama }}</td>
                                    <td>{{ $item->slug }}</td>
                                    <td>{{ number_format((int) $item->artikel_count, 0, ',', '.') }}</td>
                                    <td>{{ $item->deskripsi ?: '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('tools.artikel.kategori.destroy', $item) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">Belum ada kategori artikel.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
