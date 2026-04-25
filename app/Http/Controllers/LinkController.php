<?php

namespace App\Http\Controllers;

use App\Models\LinkPengguna;
use App\Models\User;
use App\Support\AnalitikLinkPublik;
use App\Support\PencatatLogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(
        protected AnalitikLinkPublik $analitikLinkPublik,
    ) {
    }

    public function index(Request $request): View
    {
        $pengguna = $this->pastikanProfilLinkPublik($request->user());
        $rentangHari = $this->rentangHariDipilih($request->query('rentang'));
        $source = $this->analitikLinkPublik->normalisasiSumber($request->query('source'));
        $items = $pengguna
            ->linkPengguna()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $analitik = $this->analitikLinkPublik->ringkas($pengguna, $rentangHari, $source);
        $parameterAnalitik = array_filter([
            'rentang' => $rentangHari,
            'source' => $analitik['source_filter'],
        ], fn (mixed $nilai) => filled($nilai));
        $linkAktif = $items
            ->where('aktif', true)
            ->values()
            ->map(function (LinkPengguna $link) use ($pengguna) {
                $urlPublik = $pengguna->punyaDomainKustomLink()
                    ? $pengguna->urlDomainKustomLink('link/'.$link->getKey())
                    : route('publik.link.buka', ['pengguna' => $pengguna, 'linkPengguna' => $link]);

                $link->setAttribute('url_publik', $urlPublik);

                return $link;
            });

        return view('tools.link.index', [
            'pengguna' => $pengguna,
            'items' => $items,
            'linkAktif' => $linkAktif,
            'totalLink' => $items->count(),
            'totalAktif' => $items->where('aktif', true)->count(),
            'totalNonaktif' => $items->where('aktif', false)->count(),
            'totalKlik' => $items->sum('total_klik'),
            'urlPublikUtama' => $pengguna->urlPublikLinkUtama(),
            'urlPublikDefault' => $pengguna->urlPublikLinkDefault(),
            'urlPublikDomainKustom' => $pengguna->punyaDomainKustomLink()
                ? $pengguna->urlDomainKustomLink()
                : null,
            'urlExportCsv' => route('tools.link.analitik.export', [
                ...$parameterAnalitik,
                'format' => 'csv',
            ]),
            'urlExportPdf' => route('tools.link.analitik.export', [
                ...$parameterAnalitik,
                'format' => 'pdf',
            ]),
            'targetDomainKustom' => config('link_publik.target_domain_kustom'),
            'analitik' => $analitik,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pengguna = $request->user();
        $data = $this->dataTervalidasi($request);

        $link = $pengguna->linkPengguna()->create([
            ...$data,
            'urutan' => $data['urutan'] ?? (($pengguna->linkPengguna()->max('urutan') ?? -1) + 1),
            'aktif' => true,
        ]);
        PencatatLogAktivitas::catat(
            $request,
            'link',
            'tambah',
            'Link baru ditambahkan.',
            $link,
            [
                'judul' => $link->judul,
                'url' => $link->url,
            ],
        );

        return redirect()
            ->route('tools.link')
            ->with('status', 'Link baru berhasil ditambahkan.');
    }

    public function update(Request $request, LinkPengguna $linkPengguna): RedirectResponse
    {
        abort_unless((int) $linkPengguna->user_id === (int) $request->user()->id, 404);

        $snapshotSebelum = [
            'judul' => $linkPengguna->judul,
            'url' => $linkPengguna->url,
            'aktif' => $linkPengguna->aktif,
            'urutan' => $linkPengguna->urutan,
        ];
        $data = $this->dataTervalidasi($request);

        $linkPengguna->update([
            ...$data,
            'urutan' => $data['urutan'] ?? $linkPengguna->urutan,
            'aktif' => $request->boolean('aktif'),
        ]);
        $snapshotSesudah = [
            'judul' => $linkPengguna->judul,
            'url' => $linkPengguna->url,
            'aktif' => $linkPengguna->aktif,
            'urutan' => $linkPengguna->urutan,
        ];
        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();
        PencatatLogAktivitas::catat(
            $request,
            'link',
            'ubah',
            'Link diperbarui.',
            $linkPengguna,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );

        return redirect()
            ->route('tools.link')
            ->withFragment('editor-link-'.$linkPengguna->id)
            ->with('status', 'Link berhasil diperbarui.');
    }

    public function destroy(Request $request, LinkPengguna $linkPengguna): RedirectResponse
    {
        abort_unless((int) $linkPengguna->user_id === (int) $request->user()->id, 404);

        PencatatLogAktivitas::catat(
            $request,
            'link',
            'hapus',
            'Link dihapus.',
            $linkPengguna,
            [
                'judul' => $linkPengguna->judul,
                'url' => $linkPengguna->url,
            ],
        );

        $linkPengguna->delete();

        return redirect()
            ->route('tools.link')
            ->with('status', 'Link berhasil dihapus.');
    }

    public function perbaruiProfilPublik(Request $request): RedirectResponse
    {
        $pengguna = $request->user();
        $this->pastikanProfilLinkPublik($pengguna);
        $snapshotSebelum = [
            'slug_link' => $pengguna->slug_link,
            'nama_tampil_link' => $pengguna->nama_tampil_link,
            'nomor_wa_link' => $pengguna->nomor_wa_link,
            'judul_link' => $pengguna->judul_link,
            'tema_link' => $pengguna->tema_link,
            'domain_kustom_link' => $pengguna->domain_kustom_link,
        ];

        $data = $request->validate([
            'slug_link' => [
                'required',
                'alpha_dash',
                'min:3',
                'max:40',
                Rule::unique('users', 'slug_link')->ignore($pengguna->id),
            ],
            'nama_tampil_link' => ['nullable', 'string', 'max:80'],
            'nomor_wa_link' => [
                'nullable',
                'string',
                'max:24',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && ! User::normalisasiNomorWaLink(is_string($value) ? $value : null)) {
                        $fail('Masukkan nomor WhatsApp yang valid, misalnya 081234567890 atau 6281234567890.');
                    }
                },
            ],
            'judul_link' => ['nullable', 'string', 'max:80'],
            'headline_link' => ['nullable', 'string', 'max:120'],
            'bio_link' => ['nullable', 'string', 'max:320'],
            'label_cta_link' => ['nullable', 'string', 'max:40'],
            'url_cta_link' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && ! LinkPengguna::apakahUrlEksternalValid(is_string($value) ? $value : null)) {
                        $fail('Masukkan URL CTA yang valid, misalnya https://example.com atau example.com.');
                    }
                },
            ],
            'tema_link' => ['required', Rule::in(array_keys(User::opsiTemaLink()))],
            'avatar_link' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_avatar_link' => ['nullable', 'boolean'],
            'domain_kustom_link' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('users', 'domain_kustom_link')->ignore($pengguna->id),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! filled($value)) {
                        return;
                    }

                    $host = User::normalisasiDomainKustomLink(is_string($value) ? $value : null);

                    if (! $host || ! User::apakahDomainKustomLinkValid($host)) {
                        $fail('Masukkan domain kustom yang valid, misalnya link.monitoringanda.com.');

                        return;
                    }

                    if (User::adalahHostAplikasiLinkPublik($host)) {
                        $fail('Domain kustom harus berbeda dari domain utama aplikasi.');
                    }
                },
            ],
        ]);

        $domainKustom = User::normalisasiDomainKustomLink($data['domain_kustom_link'] ?? null);
        $domainBerubah = $domainKustom !== $pengguna->domain_kustom_link;
        $avatarLink = $pengguna->avatar_link;

        if ($request->boolean('hapus_avatar_link') && filled($avatarLink)) {
            Storage::disk('public')->delete($avatarLink);
            $avatarLink = null;
        }

        if ($request->hasFile('avatar_link')) {
            if (filled($avatarLink)) {
                Storage::disk('public')->delete($avatarLink);
            }

            $avatarLink = $request->file('avatar_link')->store('avatar-link', 'public');
        }

        $pengguna->update([
            'slug_link' => Str::lower($data['slug_link']),
            'nama_tampil_link' => $data['nama_tampil_link'] ?: null,
            'nomor_wa_link' => User::normalisasiNomorWaLink($data['nomor_wa_link'] ?? null),
            'judul_link' => $data['judul_link'] ?: null,
            'headline_link' => $data['headline_link'] ?: null,
            'bio_link' => $data['bio_link'] ?: null,
            'label_cta_link' => $data['label_cta_link'] ?: null,
            'url_cta_link' => filled($data['url_cta_link'] ?? null)
                ? LinkPengguna::normalisasiUrlEksternal($data['url_cta_link'])
                : null,
            'tema_link' => $data['tema_link'],
            'avatar_link' => $avatarLink,
            'domain_kustom_link' => $domainKustom,
            'domain_kustom_terhubung_pada' => $domainBerubah
                ? null
                : $pengguna->domain_kustom_terhubung_pada,
        ]);
        $pengguna->refresh();
        $snapshotSesudah = [
            'slug_link' => $pengguna->slug_link,
            'nama_tampil_link' => $pengguna->nama_tampil_link,
            'nomor_wa_link' => $pengguna->nomor_wa_link,
            'judul_link' => $pengguna->judul_link,
            'tema_link' => $pengguna->tema_link,
            'domain_kustom_link' => $pengguna->domain_kustom_link,
        ];
        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();
        PencatatLogAktivitas::catat(
            $request,
            'link',
            'ubah',
            'Profil link publik diperbarui.',
            $pengguna,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );

        return redirect()
            ->route('tools.link')
            ->with('status', 'Profil link publik berhasil diperbarui.');
    }

    private function dataTervalidasi(Request $request): array
    {
        $urlNormal = LinkPengguna::normalisasiUrlEksternal($request->input('url'));

        $request->merge([
            'url' => $urlNormal,
        ]);

        return $request->validate([
            'judul' => ['required', 'string', 'max:60'],
            'deskripsi' => ['nullable', 'string', 'max:120'],
            'url' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! LinkPengguna::apakahUrlEksternalValid(is_string($value) ? $value : null)) {
                        $fail('Masukkan URL yang valid, misalnya https://example.com atau example.com.');
                    }
                },
            ],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
    }

    private function pastikanProfilLinkPublik(User $pengguna): User
    {
        if (filled($pengguna->slug_link)) {
            return $pengguna;
        }

        $dasar = Str::slug($pengguna->name);

        if ($dasar === '') {
            $dasar = 'pengguna';
        }

        $pengguna->update([
            'slug_link' => Str::limit($dasar, 28, '').'-'.$pengguna->id,
        ]);

        return $pengguna->refresh();
    }

    private function rentangHariDipilih(mixed $rentang): int
    {
        $rentang = (int) $rentang;

        return in_array($rentang, [7, 14, 30], true) ? $rentang : 14;
    }
}
