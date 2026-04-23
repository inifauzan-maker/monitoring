@extends('tataletak.aplikasi')

@section('judul_halaman', 'Playlist')
@section('pratitel_halaman', 'LMS')
@section('judul_konten', 'Playlist')
@section('deskripsi_halaman', 'Import playlist YouTube ke dalam kursus untuk menghasilkan materi secara otomatis.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.kursus') }}" class="btn btn-outline-secondary">
            Kursus
        </a>
        <a href="{{ route('lms.materi') }}" class="btn btn-outline-secondary">
            Materi
        </a>
    </div>
@endsection

@section('konten')
    @php($isSuperadmin = auth()->user()->adalahSuperadmin())

    <div class="row row-cards">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Import Playlist YouTube</h3>
                </div>
                <div class="card-body">
                    @if ($isSuperadmin)
                        <form method="POST" action="{{ route('lms.playlist.store') }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="kursus_id">Pilih Kursus</label>
                                    <select class="form-select" id="kursus_id" name="kursus_id" required>
                                        <option value="">Pilih kursus</option>
                                        @foreach ($opsiKursus as $kursus)
                                            <option value="{{ $kursus->id }}" @selected((int) old('kursus_id') === $kursus->id)>
                                                {{ $kursus->judul }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="playlist_url">URL / ID Playlist</label>
                                    <input type="text" class="form-control" id="playlist_url" name="playlist_url"
                                        value="{{ old('playlist_url') }}" required
                                        placeholder="https://www.youtube.com/playlist?list=...">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="durasi_default_detik">Durasi Default (detik)</label>
                                    <input type="number" class="form-control" id="durasi_default_detik" name="durasi_default_detik"
                                        value="{{ old('durasi_default_detik', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">Import Playlist</button>
                            </div>
                        </form>
                    @else
                        <div class="empty py-5">
                            <p class="empty-title">Import playlist hanya untuk superadmin</p>
                            <p class="empty-subtitle text-secondary mb-0">
                                Anda tetap bisa melihat modul ini, tetapi proses import dibatasi untuk superadmin.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Catatan Import</h3>
                </div>
                <div class="card-body">
                    <div class="text-secondary">
                        <p class="mb-3">
                            Modul ini mengikuti pola import playlist dari project referensi `kursusonline`.
                        </p>
                        <ul class="mb-3 ps-3">
                            <li>Pilih kursus tujuan sebelum import.</li>
                            <li>Tempel URL playlist YouTube atau cukup ID playlist.</li>
                            <li>Video yang sudah pernah masuk ke kursus yang sama akan dilewati.</li>
                            <li>Durasi default dipakai saat durasi asli video belum diproses lebih lanjut.</li>
                        </ul>
                        <p class="mb-0">
                            Pastikan ENV <code>YOUTUBE_API_KEY</code> terisi agar import bisa berjalan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
