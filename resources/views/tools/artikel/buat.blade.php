@extends('tataletak.aplikasi')

@section('judul_halaman', 'Buat Artikel')
@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Buat Artikel')
@section('deskripsi_halaman', 'Susun draft artikel baru dengan metadata SEO, referensi, dan struktur konten yang rapi.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.kategori.index') }}" class="btn btn-outline-secondary">Kategori</a>
        <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-secondary">Artikel Saya</a>
    </div>
@endsection

@section('konten')
    @include('tools.artikel.formulir', [
        'formAction' => route('tools.artikel.store'),
        'formMethod' => 'POST',
        'kategori' => $kategori,
        'artikel' => null,
    ])
@endsection
