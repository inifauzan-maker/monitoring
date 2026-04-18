<?php

namespace App\Http\Controllers;

use App\Support\AnalitikLinkPublik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LinkAnalitikExportController extends Controller
{
    public function __construct(
        protected AnalitikLinkPublik $analitikLinkPublik,
    ) {
    }

    public function __invoke(Request $request): StreamedResponse|Response
    {
        $validasi = $request->validate([
            'rentang' => ['nullable', 'integer', Rule::in([7, 14, 30])],
            'source' => ['nullable', 'string', 'max:120'],
            'format' => ['nullable', Rule::in(['csv', 'pdf'])],
        ]);

        $rentang = (int) ($validasi['rentang'] ?? 14);
        $format = $validasi['format'] ?? 'csv';
        $source = $validasi['source'] ?? null;
        $pengguna = $request->user();
        $analitik = $this->analitikLinkPublik->ringkas($pengguna, $rentang, $source);
        $slugPengguna = Str::slug($pengguna->slug_link ?: $pengguna->name ?: 'pengguna');
        $namaBerkas = 'analitik-link-'.$slugPengguna.'-'.now()->format('Y-m-d');

        return $format === 'pdf'
            ? $this->eksporPdf($pengguna, $analitik, $namaBerkas)
            : $this->eksporCsv($pengguna, $analitik, $namaBerkas);
    }

    private function eksporCsv(object $pengguna, array $analitik, string $namaBerkas): StreamedResponse
    {
        return response()->streamDownload(function () use ($pengguna, $analitik): void {
            $stream = fopen('php://output', 'wb');

            if (! $stream) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, ['Ringkasan Analitik Link']);
            fputcsv($stream, ['Pengguna', $pengguna->name]);
            fputcsv($stream, ['Periode', $analitik['periode_label']]);
            fputcsv($stream, ['Source', $analitik['source_label']]);
            fputcsv($stream, []);

            fputcsv($stream, ['Metrik', 'Nilai']);
            fputcsv($stream, ['Kunjungan Halaman', $analitik['metrik']['kunjungan_halaman']]);
            fputcsv($stream, ['Klik CTA', $analitik['metrik']['klik_cta']]);
            fputcsv($stream, ['Klik Link', $analitik['metrik']['klik_link']]);
            fputcsv($stream, ['Rasio Interaksi', $analitik['metrik']['rasio_interaksi'].'%']);
            fputcsv($stream, []);

            fputcsv($stream, ['Statistik Harian']);
            fputcsv($stream, ['Tanggal', 'Kunjungan', 'Klik CTA', 'Klik Link']);

            foreach ($analitik['harian'] as $hari) {
                fputcsv($stream, [
                    $hari['tanggal'],
                    $hari['kunjungan_halaman'],
                    $hari['klik_cta'],
                    $hari['klik_link'],
                ]);
            }

            fputcsv($stream, []);
            fputcsv($stream, ['Traffic Source']);
            fputcsv($stream, ['Source', 'Jumlah', 'Share']);

            foreach ($analitik['top_sumber'] as $sumber) {
                fputcsv($stream, [
                    $sumber['label'],
                    $sumber['count'],
                    $sumber['share'].'%',
                ]);
            }

            fputcsv($stream, []);
            fputcsv($stream, ['Top Link']);
            fputcsv($stream, ['Judul', 'URL', 'Klik', 'Share']);

            foreach ($analitik['top_link'] as $itemTop) {
                fputcsv($stream, [
                    $itemTop['link']->judul,
                    $itemTop['link']->urlTujuan(),
                    $itemTop['total_klik'],
                    $itemTop['porsi_klik'].'%',
                ]);
            }

            fclose($stream);
        }, $namaBerkas.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function eksporPdf(object $pengguna, array $analitik, string $namaBerkas): Response
    {
        return Pdf::loadView('tools.link.pdf.analitik', [
            'pengguna' => $pengguna,
            'analitik' => $analitik,
        ])
            ->setPaper('a4', 'portrait')
            ->download($namaBerkas.'.pdf');
    }
}
