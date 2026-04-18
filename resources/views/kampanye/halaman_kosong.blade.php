@extends('tataletak.aplikasi')

@section('judul_halaman', $judulHalaman)
@section('pratitel_halaman', 'Kampanye')
@section('judul_konten', $judulHalaman)
@section('deskripsi_halaman', $deskripsiHalaman)

@section('konten')
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty py-6">
                        <div class="empty-img mb-3">
                            <svg class="icon icon-lg text-secondary" xmlns="http://www.w3.org/2000/svg" width="64"
                                height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 19l16 0" />
                                <path d="M4 15l4 -6l4 2l4 -5l4 4" />
                                <path d="M4 12l0 7" />
                                <path d="M20 10l0 9" />
                            </svg>
                        </div>
                        <p class="empty-title">{{ $judulHalaman }}</p>
                        <p class="empty-subtitle text-secondary">
                            Halaman ini masih dikosongkan sementara. Struktur menu dan route sudah siap untuk
                            pengembangan berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
