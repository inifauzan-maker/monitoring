@extends('tataletak.aplikasi')

@section('judul_halaman', $judulHalaman)
@section('pratitel_halaman', 'LMS')
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
                                <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                                <path d="M8 5v14" />
                                <path d="M12 9h4" />
                                <path d="M12 13h4" />
                            </svg>
                        </div>
                        <p class="empty-title">{{ $judulHalaman }}</p>
                        <p class="empty-subtitle text-secondary">
                            {{ $deskripsiHalaman }} Modul ini masih dikosongkan sementara sambil menyiapkan adaptasi
                            fitur dari project referensi `kursusonline`.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
