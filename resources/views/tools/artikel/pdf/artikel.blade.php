<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $artikel->judul }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.65; }
        h1, h2, h3 { color: #111827; margin-bottom: 8px; }
        .muted { color: #6b7280; }
        .meta { margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #d1d5db; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #e5e7eb; font-size: 10px; margin-right: 6px; }
        .section { margin-top: 22px; }
        ul { padding-left: 18px; }
    </style>
</head>
<body>
    <div class="meta">
        <div class="badge">{{ $artikel->kategori?->nama ?: 'Umum' }}</div>
        <div class="badge">{{ $artikel->labelTingkatKeahlian() }}</div>
        <div class="badge">{{ $artikel->labelStatusPublikasi() }}</div>
        @if ($adalahPreview)
            <div class="muted" style="margin-top: 8px;">Dokumen ini berasal dari mode preview internal.</div>
        @endif
        <h1>{{ $artikel->judul }}</h1>
        <div class="muted">
            Penulis: {{ $artikel->penulis?->name ?: 'Penulis belum diatur' }}<br>
            @if ($artikel->sedangTerjadwal())
                Jadwal terbit: {{ $artikel->diterbitkan_pada?->format('d M Y H:i') ?: '-' }}
            @elseif ($artikel->diterbitkan_pada)
                Tanggal terbit: {{ $artikel->diterbitkan_pada->format('d M Y H:i') }}
            @else
                Status: Draft
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Ringkasan</h2>
        <div>{{ $artikel->ringkasan }}</div>
    </div>

    <div class="section">
        <h2>Konten</h2>
        <div>{!! $artikel->konten !!}</div>
    </div>

    @if ($artikel->bio_penulis)
        <div class="section">
            <h2>Bio Penulis</h2>
            <div>{{ $artikel->bio_penulis }}</div>
        </div>
    @endif

    @if (filled($artikel->sumber_referensi))
        <div class="section">
            <h2>Sumber Referensi</h2>
            <ul>
                @foreach ($artikel->sumber_referensi as $sumber)
                    <li>{{ $sumber }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>
</html>
