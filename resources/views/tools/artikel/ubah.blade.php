@extends('tataletak.aplikasi')

@section('judul_halaman', 'Ubah Artikel')
@section('pratitel_halaman', 'Tools')
@section('judul_konten', 'Ubah Artikel')
@section('deskripsi_halaman', 'Perbarui draft, kelola metadata, dan terbitkan artikel saat semua data sudah siap.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('tools.artikel.editorial') }}" class="btn btn-outline-secondary">Dashboard Editorial</a>
        <a href="{{ route('tools.artikel.saya') }}" class="btn btn-outline-secondary">Artikel Saya</a>
        <a href="{{ route('tools.artikel.kategori.index') }}" class="btn btn-outline-secondary">Kategori</a>
        <a href="{{ route('tools.artikel.preview', $artikel) }}" class="btn btn-outline-primary">Preview</a>
        <a href="{{ route('tools.artikel.pdf', $artikel) }}" class="btn btn-outline-secondary">Export PDF</a>
        @if ($artikel->adalahDraft())
            <form method="POST" action="{{ route('tools.artikel.terbitkan', $artikel) }}">
                @csrf
                <button type="submit" class="btn btn-success">
                    {{ $artikel->diterbitkan_pada?->isFuture() ? 'Jadwalkan Terbit' : 'Terbitkan' }}
                </button>
            </form>
        @else
            @if ($artikel->sudahTerbitAktif())
                <a href="{{ route('tools.artikel.show', $artikel) }}" class="btn btn-success">Buka Artikel</a>
            @endif
            <form method="POST" action="{{ route('tools.artikel.batalkan_terbit', $artikel) }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Unpublish</button>
            </form>
        @endif
    </div>
@endsection

@section('konten')
    @include('tools.artikel.formulir', [
        'formAction' => route('tools.artikel.update', $artikel),
        'formMethod' => 'PUT',
        'kategori' => $kategori,
        'artikel' => $artikel,
    ])
@endsection
