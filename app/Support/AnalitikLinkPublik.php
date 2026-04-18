<?php

namespace App\Support;

use App\Models\AktivitasLinkPublik;
use App\Models\LinkPengguna;
use App\Models\StatistikLinkHarian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AnalitikLinkPublik
{
    public function catat(
        User $pengguna,
        string $jenisAktivitas,
        Request $request,
        ?LinkPengguna $linkPengguna = null,
        ?string $urlTujuan = null,
        ?Carbon $waktu = null,
    ): void {
        $waktu ??= now();
        $sumberTraffic = $this->sumberDariRequest($request);

        AktivitasLinkPublik::create([
            'user_id' => $pengguna->id,
            'link_pengguna_id' => $linkPengguna?->id,
            'jenis_aktivitas' => $jenisAktivitas,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'sumber_traffic' => $sumberTraffic,
            'url_tujuan' => $urlTujuan,
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        $statistik = StatistikLinkHarian::query()
            ->where('user_id', $pengguna->id)
            ->where('jenis_aktivitas', $jenisAktivitas)
            ->whereDate('tanggal', $waktu->toDateString())
            ->when(
                $linkPengguna,
                fn ($query) => $query->where('link_pengguna_id', $linkPengguna->id),
                fn ($query) => $query->whereNull('link_pengguna_id'),
            )
            ->first();

        if (! $statistik) {
            $statistik = new StatistikLinkHarian([
                'user_id' => $pengguna->id,
                'link_pengguna_id' => $linkPengguna?->id,
                'jenis_aktivitas' => $jenisAktivitas,
                'tanggal' => $waktu->toDateString(),
                'total' => 0,
            ]);
        }

        $statistik->total += 1;
        $statistik->save();
    }

    public function ringkas(User $pengguna, int $rentangHari = 14, ?string $source = null): array
    {
        $opsiRentang = [7, 14, 30];
        $rentangHari = in_array($rentangHari, $opsiRentang, true) ? $rentangHari : 14;
        $source = $this->normalisasiSumber($source);
        $mulai = now()->subDays($rentangHari - 1)->startOfDay();
        $selesai = now()->endOfDay();

        $aktivitas = AktivitasLinkPublik::query()
            ->where('user_id', $pengguna->id)
            ->whereBetween('created_at', [$mulai, $selesai])
            ->when($source !== null, fn ($query) => $query->where('sumber_traffic', $source))
            ->get([
                'id',
                'link_pengguna_id',
                'jenis_aktivitas',
                'sumber_traffic',
                'created_at',
            ]);

        $aktivitasPerHari = $aktivitas->groupBy(fn (AktivitasLinkPublik $item) => $item->created_at->toDateString());

        $harian = Collection::times($rentangHari, function (int $offset) use ($mulai, $aktivitasPerHari) {
            $tanggal = $mulai->copy()->addDays($offset - 1)->toDateString();
            $aktivitasHari = $aktivitasPerHari->get($tanggal, collect());

            return [
                'tanggal' => $tanggal,
                'label' => Carbon::parse($tanggal)->format('d M'),
                'kunjungan_halaman' => $aktivitasHari->where('jenis_aktivitas', StatistikLinkHarian::KUNJUNGAN_HALAMAN)->count(),
                'klik_cta' => $aktivitasHari->where('jenis_aktivitas', StatistikLinkHarian::KLIK_CTA)->count(),
                'klik_link' => $aktivitasHari->where('jenis_aktivitas', StatistikLinkHarian::KLIK_LINK)->count(),
            ];
        });

        $kunjunganHalaman = $aktivitas->where('jenis_aktivitas', StatistikLinkHarian::KUNJUNGAN_HALAMAN)->count();
        $klikCta = $aktivitas->where('jenis_aktivitas', StatistikLinkHarian::KLIK_CTA)->count();
        $klikLink = $aktivitas->where('jenis_aktivitas', StatistikLinkHarian::KLIK_LINK)->count();
        $rasioInteraksi = $kunjunganHalaman > 0
            ? round((($klikCta + $klikLink) / $kunjunganHalaman) * 100, 1)
            : 0;

        $topLink = $pengguna->linkPengguna()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function (LinkPengguna $link) use ($aktivitas, $klikLink) {
                $totalKlikPeriode = $aktivitas
                    ->where('jenis_aktivitas', StatistikLinkHarian::KLIK_LINK)
                    ->where('link_pengguna_id', $link->id)
                    ->count();

                return [
                    'link' => $link,
                    'total_klik' => $totalKlikPeriode,
                    'porsi_klik' => $klikLink > 0 ? round(($totalKlikPeriode / $klikLink) * 100, 1) : 0,
                ];
            })
            ->sort(function (array $kiri, array $kanan) {
                $berdasarkanKlik = $kanan['total_klik'] <=> $kiri['total_klik'];

                if ($berdasarkanKlik !== 0) {
                    return $berdasarkanKlik;
                }

                return $kiri['link']->urutan <=> $kanan['link']->urutan;
            })
            ->filter(fn (array $item) => $item['total_klik'] > 0)
            ->values()
            ->take(5);

        $topSumber = $this->hitungSumber(
            $aktivitas->where('jenis_aktivitas', StatistikLinkHarian::KUNJUNGAN_HALAMAN)
        )->take(5)->values();

        $sumberTersedia = $this->sumberTersedia($pengguna, $mulai, $selesai);

        return [
            'rentang_hari' => $rentangHari,
            'opsi_rentang' => $opsiRentang,
            'periode_label' => $rentangHari.' hari terakhir',
            'source_filter' => $source,
            'source_label' => $source ?? 'Semua source',
            'sumber_tersedia' => $sumberTersedia,
            'metrik' => [
                'kunjungan_halaman' => $kunjunganHalaman,
                'klik_cta' => $klikCta,
                'klik_link' => $klikLink,
                'rasio_interaksi' => $rasioInteraksi,
            ],
            'harian' => $harian,
            'top_link' => $topLink,
            'top_sumber' => $topSumber,
            'maksimum_harian' => max(
                1,
                (int) $harian->max(fn (array $hari) => max($hari['kunjungan_halaman'], $hari['klik_cta'], $hari['klik_link']))
            ),
        ];
    }

    public function sumberTersedia(User $pengguna, Carbon $mulai, Carbon $selesai): Collection
    {
        $aktivitas = AktivitasLinkPublik::query()
            ->where('user_id', $pengguna->id)
            ->where('jenis_aktivitas', StatistikLinkHarian::KUNJUNGAN_HALAMAN)
            ->whereBetween('created_at', [$mulai, $selesai])
            ->get(['sumber_traffic']);

        return $this->hitungSumber($aktivitas);
    }

    public function normalisasiSumber(?string $nilai): ?string
    {
        if (! filled($nilai)) {
            return null;
        }

        $normal = trim(Str::lower((string) $nilai));

        if ($normal === '') {
            return null;
        }

        if (Str::contains($normal, '://')) {
            $host = parse_url($normal, PHP_URL_HOST);

            if (! is_string($host) || $host === '') {
                return null;
            }

            return Str::replaceStart('www.', '', Str::lower($host));
        }

        return Str::replaceStart('www.', '', $normal);
    }

    private function sumberDariRequest(Request $request): string
    {
        $referensi = $request->query('utm_source')
            ?? $request->query('source')
            ?? $request->headers->get('referer');

        return $this->normalisasiReferrer($referensi);
    }

    private function hitungSumber(Collection $aktivitas): Collection
    {
        $total = $aktivitas->count();

        return $aktivitas
            ->map(fn ($item) => $this->normalisasiReferrer($item->sumber_traffic))
            ->countBy()
            ->sortDesc()
            ->map(fn (int $count, string $label) => [
                'label' => $label,
                'count' => $count,
                'share' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ])
            ->values();
    }

    private function normalisasiReferrer(?string $referrer): string
    {
        $normal = $this->normalisasiSumber($referrer);

        return $normal ?? 'direct / unknown';
    }
}
