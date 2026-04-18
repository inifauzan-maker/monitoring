<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Analitik Link</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
            h1, h2, h3 { margin: 0; }
            .muted { color: #64748b; }
            .section { margin-top: 20px; }
            .cards { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin: 12px -10px 0; }
            .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; vertical-align: top; }
            th { background: #eff6ff; }
            .right { text-align: right; }
        </style>
    </head>
    <body>
        <h1>Analitik Link Publik</h1>
        <div class="muted">{{ $pengguna->name }} | {{ $analitik['periode_label'] }} | Source: {{ $analitik['source_label'] }}</div>

        <table class="cards">
            <tr>
                <td class="card">
                    <div class="muted">Kunjungan Halaman</div>
                    <div><strong>{{ number_format($analitik['metrik']['kunjungan_halaman']) }}</strong></div>
                </td>
                <td class="card">
                    <div class="muted">Klik CTA</div>
                    <div><strong>{{ number_format($analitik['metrik']['klik_cta']) }}</strong></div>
                </td>
                <td class="card">
                    <div class="muted">Klik Link</div>
                    <div><strong>{{ number_format($analitik['metrik']['klik_link']) }}</strong></div>
                </td>
                <td class="card">
                    <div class="muted">Rasio Interaksi</div>
                    <div><strong>{{ number_format($analitik['metrik']['rasio_interaksi'], 1) }}%</strong></div>
                </td>
            </tr>
        </table>

        <div class="section">
            <h3>Statistik Harian</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="right">Kunjungan</th>
                        <th class="right">Klik CTA</th>
                        <th class="right">Klik Link</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($analitik['harian'] as $hari)
                        <tr>
                            <td>{{ $hari['tanggal'] }}</td>
                            <td class="right">{{ number_format($hari['kunjungan_halaman']) }}</td>
                            <td class="right">{{ number_format($hari['klik_cta']) }}</td>
                            <td class="right">{{ number_format($hari['klik_link']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Traffic Source</h3>
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th class="right">Jumlah</th>
                        <th class="right">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analitik['top_sumber'] as $sumber)
                        <tr>
                            <td>{{ $sumber['label'] }}</td>
                            <td class="right">{{ number_format($sumber['count']) }}</td>
                            <td class="right">{{ number_format($sumber['share'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Belum ada data source.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Top Link</h3>
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>URL</th>
                        <th class="right">Klik</th>
                        <th class="right">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analitik['top_link'] as $itemTop)
                        <tr>
                            <td>{{ $itemTop['link']->judul }}</td>
                            <td>{{ $itemTop['link']->urlTujuan() }}</td>
                            <td class="right">{{ number_format($itemTop['total_klik']) }}</td>
                            <td class="right">{{ number_format($itemTop['porsi_klik'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Belum ada data klik link.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </body>
</html>
