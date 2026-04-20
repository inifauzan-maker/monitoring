<?php

namespace App\Http\Controllers;

use App\Models\LinkPengguna;
use App\Models\StatistikLinkHarian;
use App\Models\User;
use App\Support\AnalitikLinkPublik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkPublikController extends Controller
{
    public function __construct(
        protected AnalitikLinkPublik $analitikLinkPublik,
    ) {
    }

    public function root(Request $request): View|RedirectResponse
    {
        $pengguna = User::resolusikanDariDomainKustomLink($request->getHost());

        if ($pengguna) {
            return $this->renderHalamanPublik($request, $pengguna, true);
        }

        if ($request->user()) {
            return view('dasbor.beranda');
        }

        return redirect()->route('login');
    }

    public function show(Request $request, User $pengguna): View
    {
        return $this->renderHalamanPublik(
            $request,
            $pengguna,
            $pengguna->cocokDomainKustomLink($request->getHost())
        );
    }

    public function cta(Request $request, User $pengguna): RedirectResponse
    {
        return $this->prosesCta(
            $request,
            $pengguna,
            $pengguna->cocokDomainKustomLink($request->getHost())
        );
    }

    public function domainCta(Request $request): RedirectResponse
    {
        return $this->prosesCta($request, $this->penggunaDariDomainKustom($request), true);
    }

    public function buka(Request $request, User $pengguna, LinkPengguna $linkPengguna): RedirectResponse
    {
        return $this->prosesBuka(
            $request,
            $pengguna,
            $linkPengguna,
            $pengguna->cocokDomainKustomLink($request->getHost())
        );
    }

    public function domainBuka(Request $request, LinkPengguna $linkPengguna): RedirectResponse
    {
        return $this->prosesBuka($request, $this->penggunaDariDomainKustom($request), $linkPengguna, true);
    }

    private function renderHalamanPublik(Request $request, User $pengguna, bool $menggunakanDomainKustom): View
    {
        $pengguna = $this->siapkanPenggunaPublik($request, $pengguna, $menggunakanDomainKustom);
        $sourceAktif = $this->analitikLinkPublik->normalisasiSumber(
            $request->query('source') ?? $request->query('utm_source')
        );
        $parameterSource = array_filter([
            'source' => $sourceAktif,
        ], fn (mixed $nilai) => filled($nilai));
        $linkAktif = $pengguna
            ->linkPengguna()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function (LinkPengguna $link) use ($pengguna, $menggunakanDomainKustom, $parameterSource) {
                $urlPublik = $menggunakanDomainKustom
                    ? route('publik.domain_link.buka', [
                        'linkPengguna' => $link,
                        ...$parameterSource,
                    ], false)
                    : route('publik.link.buka', [
                        'pengguna' => $pengguna,
                        'linkPengguna' => $link,
                        ...$parameterSource,
                    ]);

                $link->setAttribute('url_publik', $urlPublik);

                return $link;
            });
        $urlCtaPublik = null;

        if ($pengguna->urlCtaLinkPublik()) {
            $urlCtaPublik = $menggunakanDomainKustom
                ? route('publik.domain_link.cta', $parameterSource, false)
                : route('publik.link.cta', [
                    'pengguna' => $pengguna,
                    ...$parameterSource,
                ]);
        }

        $this->analitikLinkPublik->catat($pengguna, StatistikLinkHarian::KUNJUNGAN_HALAMAN, $request);

        return view('tools.link.publik', [
            'pengguna' => $pengguna,
            'linkAktif' => $linkAktif,
            'judulHalaman' => $pengguna->judulLinkPublik(),
            'namaPublik' => $pengguna->namaTampilLinkPublik(),
            'avatarPublikUrl' => $pengguna->avatarLinkPublikUrl(),
            'headlineHalaman' => $pengguna->headlineLinkPublik(),
            'bioHalaman' => $pengguna->bioLinkPublik(),
            'labelCta' => $pengguna->labelCtaLinkPublik(),
            'urlCta' => $pengguna->urlCtaLinkPublik(),
            'urlCtaPublik' => $urlCtaPublik,
            'temaHalaman' => $pengguna->temaLinkKonfigurasi(),
            'sourceAktif' => $sourceAktif,
            'menggunakanDomainKustom' => $menggunakanDomainKustom,
        ]);
    }

    private function prosesCta(Request $request, User $pengguna, bool $menggunakanDomainKustom): RedirectResponse
    {
        $pengguna = $this->siapkanPenggunaPublik($request, $pengguna, $menggunakanDomainKustom);
        $tujuan = $pengguna->urlCtaLinkPublik();

        abort_unless($tujuan && LinkPengguna::apakahUrlEksternalValid($tujuan), 404);

        $this->analitikLinkPublik->catat(
            $pengguna,
            StatistikLinkHarian::KLIK_CTA,
            $request,
            urlTujuan: $tujuan,
        );

        return redirect()->away($tujuan);
    }

    private function prosesBuka(
        Request $request,
        User $pengguna,
        LinkPengguna $linkPengguna,
        bool $menggunakanDomainKustom,
    ): RedirectResponse {
        $pengguna = $this->siapkanPenggunaPublik($request, $pengguna, $menggunakanDomainKustom);
        abort_if((int) $linkPengguna->user_id !== (int) $pengguna->id || ! $linkPengguna->aktif, 404);

        $tujuan = $linkPengguna->urlTujuan();

        abort_unless(LinkPengguna::apakahUrlEksternalValid($tujuan), 404);

        $linkPengguna->increment('total_klik');
        $linkPengguna->forceFill([
            'terakhir_diklik_pada' => now(),
        ])->save();
        $this->analitikLinkPublik->catat(
            $pengguna,
            StatistikLinkHarian::KLIK_LINK,
            $request,
            $linkPengguna,
            $tujuan,
        );

        return redirect()->away($tujuan);
    }

    private function penggunaDariDomainKustom(Request $request): User
    {
        $pengguna = User::resolusikanDariDomainKustomLink($request->getHost());

        abort_unless($pengguna, 404);

        return $pengguna;
    }

    private function siapkanPenggunaPublik(Request $request, User $pengguna, bool $menggunakanDomainKustom): User
    {
        if ($menggunakanDomainKustom) {
            $pengguna->tandaiDomainKustomTerhubung($request->getHost());
        }

        return $pengguna;
    }
}
