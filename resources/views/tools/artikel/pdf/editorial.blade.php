<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dashboard Editorial Artikel</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.55; }
        h1, h2, h3 { color: #111827; margin-bottom: 8px; }
        .grid { width: 100%; margin-bottom: 16px; }
        .grid td { width: 33.33%; vertical-align: top; padding: 8px; }
        .card { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; }
        .label { font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .value { font-size: 22px; font-weight: bold; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; text-align: left; }
        th { background: #f3f4f6; }
        .muted { color: #6b7280; }
        .section { margin-top: 18px; }
    </style>
</head>
<body>
    <h1>Dashboard Editorial Artikel</h1>
    <div class="muted">Dicetak pada {{ now()->format('d M Y H:i') }}</div>
    @if ($filterAktif)
        <div class="muted" style="margin-top: 6px;">
            Filter:
            @if ($presetEditorialDipilih)
                Preset {{ $opsiPresetEditorial[$presetEditorialDipilih]['label'] }}
            @endif
            @if ($presetPenggunaDipilih)
                Preset Kustom {{ $presetPenggunaDipilih->nama_preset }}
            @endif
            @if (($presetEditorialDipilih || $presetPenggunaDipilih) && ($penulisDipilih || $kategoriDipilih || $statusEditorialDipilih || $filterTanggalAktif))
                |
            @endif
            @if ($penulisDipilih)
                Penulis {{ $penulisDipilih->name }}
            @endif
            @if ($penulisDipilih && $kategoriDipilih)
                |
            @endif
            @if ($kategoriDipilih)
                Kategori {{ $kategoriDipilih->nama }}
            @endif
            @if (($penulisDipilih || $kategoriDipilih) && $statusEditorialDipilih)
                |
            @endif
            @if ($statusEditorialDipilih)
                Status {{ $opsiStatusEditorial[$statusEditorialDipilih] }}
            @endif
            @if (($penulisDipilih || $kategoriDipilih || $statusEditorialDipilih) && $filterTanggalAktif)
                |
            @endif
            @if ($filterTanggalAktif)
                {{ $labelFilterTanggal }}
            @endif
        </div>
    @endif

    <table class="grid" role="presentation">
        <tr>
            <td><div class="card"><div class="label">Total Artikel</div><div class="value">{{ number_format($jumlahSemua, 0, ',', '.') }}</div></div></td>
            <td><div class="card"><div class="label">Draft</div><div class="value">{{ number_format($jumlahDraft, 0, ',', '.') }}</div></div></td>
            <td><div class="card"><div class="label">Terjadwal</div><div class="value">{{ number_format($jumlahTerjadwal, 0, ',', '.') }}</div></div></td>
        </tr>
        <tr>
            <td><div class="card"><div class="label">Terbit Aktif</div><div class="value">{{ number_format($jumlahTerbitAktif, 0, ',', '.') }}</div></div></td>
            <td><div class="card"><div class="label">Siap Terbit</div><div class="value">{{ number_format($jumlahSiapTerbit, 0, ',', '.') }}</div></div></td>
            <td><div class="card"><div class="label">Perlu Revisi</div><div class="value">{{ number_format($jumlahPerluRevisi, 0, ',', '.') }}</div></div></td>
        </tr>
    </table>

    <div class="section">
        <h2>Notifikasi Editorial</h2>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($notifikasiEditorial as $notifikasi)
                    <tr>
                        <td>{{ $notifikasi['judul'] }}</td>
                        <td>{{ $notifikasi['pesan'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Jadwal Terbit Terdekat</h2>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Jadwal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artikelTerjadwal as $item)
                    <tr>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->penulis?->name ?: '-' }}</td>
                        <td>{{ $item->diterbitkan_pada?->format('d M Y H:i') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada artikel terjadwal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Artikel Siap Terbit</h2>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Skor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artikelSiapTerbit as $item)
                    <tr>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->kategori?->nama ?: '-' }}</td>
                        <td>{{ $item->evaluasiKesiapan()['skor'] }}/100</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada draft yang siap terbit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Artikel Perlu Tindak Lanjut</h2>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Skor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artikelPerluRevisi as $item)
                    <tr>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->labelStatusPublikasi() }}</td>
                        <td>{{ $item->evaluasiKesiapan()['skor'] }}/100</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada artikel yang perlu tindak lanjut.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
