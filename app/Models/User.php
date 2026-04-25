<?php

namespace App\Models;

use App\Enums\LevelAksesPengguna;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'email',
    'slug_link',
    'nama_tampil_link',
    'nomor_wa_link',
    'judul_link',
    'headline_link',
    'bio_link',
    'label_cta_link',
    'url_cta_link',
    'tema_link',
    'avatar_link',
    'domain_kustom_link',
    'domain_kustom_terhubung_pada',
    'password',
    'level_akses',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'level_akses' => LevelAksesPengguna::class,
            'domain_kustom_terhubung_pada' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function adalahSuperadmin(): bool
    {
        return $this->level_akses === LevelAksesPengguna::SUPERADMIN;
    }

    public function punyaAksesMinimal(string|LevelAksesPengguna $levelAkses): bool
    {
        $levelAkses = is_string($levelAkses)
            ? LevelAksesPengguna::from($levelAkses)
            : $levelAkses;

        return $this->level_akses?->urutan() <= $levelAkses->urutan();
    }

    public function labelLevelAkses(): string
    {
        return $this->level_akses?->label() ?? '-';
    }

    public function kelasBadgeLevelAkses(): string
    {
        return $this->level_akses?->kelasBadge() ?? 'bg-secondary-lt text-secondary';
    }

    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class, 'penulis_id');
    }

    public function presetEditorialPengguna(): HasMany
    {
        return $this->hasMany(PresetEditorialPengguna::class, 'user_id');
    }

    public function linkPengguna(): HasMany
    {
        return $this->hasMany(LinkPengguna::class, 'user_id');
    }

    public function statistikLinkHarian(): HasMany
    {
        return $this->hasMany(StatistikLinkHarian::class, 'user_id');
    }

    public function aktivitasLinkPublik(): HasMany
    {
        return $this->hasMany(AktivitasLinkPublik::class, 'user_id');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }

    public function notifikasiPengguna(): HasMany
    {
        return $this->hasMany(NotifikasiPengguna::class, 'user_id')->latest();
    }

    public function progresBelajarMateri(): HasMany
    {
        return $this->hasMany(ProgresBelajarMateri::class, 'user_id');
    }

    public function hasilKuisPengguna(): HasMany
    {
        return $this->hasMany(HasilKuisPengguna::class, 'user_id');
    }

    public function percobaanKuisPengguna(): HasMany
    {
        return $this->hasMany(PercobaanKuisPengguna::class, 'user_id')->latest('percobaan_ke');
    }

    public function proyekDibuat(): HasMany
    {
        return $this->hasMany(Proyek::class, 'dibuat_oleh');
    }

    public function proyekDitanggungjawabi(): HasMany
    {
        return $this->hasMany(Proyek::class, 'penanggung_jawab_id');
    }

    public function tugasProyekDitanggungjawabi(): HasMany
    {
        return $this->hasMany(TugasProyek::class, 'penanggung_jawab_id');
    }

    public function judulLinkPublik(): string
    {
        return $this->judul_link ?: $this->namaTampilLinkPublik();
    }

    public function namaTampilLinkPublik(): string
    {
        return $this->nama_tampil_link ?: $this->judul_link ?: $this->name;
    }

    public function nomorWaLinkPublik(): ?string
    {
        return self::normalisasiNomorWaLink($this->nomor_wa_link);
    }

    public function nomorWaLinkTampil(): ?string
    {
        $nomor = $this->nomorWaLinkPublik();

        if (! $nomor) {
            return null;
        }

        return Str::of($nomor)
            ->replaceMatches('/(\d{4})(?=\d)/', '$1 ')
            ->trim()
            ->value();
    }

    public function urlWaLinkPublik(): ?string
    {
        $nomor = $this->nomorWaLinkPublik();

        return $nomor ? 'https://wa.me/'.$nomor : null;
    }

    public function inisialLinkPublik(): string
    {
        $inisial = Str::of($this->namaTampilLinkPublik())
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $bagian) => Str::upper(Str::substr($bagian, 0, 1)))
            ->implode('');

        return $inisial !== '' ? $inisial : 'LP';
    }

    public function headlineLinkPublik(): ?string
    {
        return $this->headline_link ?: null;
    }

    public function bioLinkPublik(): ?string
    {
        return $this->bio_link ?: null;
    }

    public function labelCtaLinkPublik(): string
    {
        return $this->label_cta_link ?: 'Buka Tautan Utama';
    }

    public function urlCtaLinkPublik(): ?string
    {
        return LinkPengguna::normalisasiUrlEksternal($this->url_cta_link);
    }

    public function avatarLinkPublikUrl(): ?string
    {
        if (! filled($this->avatar_link)) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->avatar_link)) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_link);
    }

    public function temaLinkKonfigurasi(): array
    {
        $opsi = self::opsiTemaLink();

        return $opsi[$this->tema_link] ?? $opsi['sunset'];
    }

    public function punyaDomainKustomLink(): bool
    {
        return filled($this->domain_kustom_link);
    }

    public function urlPublikLinkDefault(): string
    {
        return route('publik.link.show', $this);
    }

    public function urlDomainKustomLink(?string $path = null): string
    {
        $skema = config('link_publik.skema_domain_kustom', 'https');
        $path = trim((string) $path, '/');

        return $skema.'://'.$this->domain_kustom_link.($path === '' ? '' : '/'.$path);
    }

    public function urlPublikLinkUtama(): string
    {
        return $this->punyaDomainKustomLink()
            ? $this->urlDomainKustomLink()
            : $this->urlPublikLinkDefault();
    }

    public function cocokDomainKustomLink(?string $host): bool
    {
        return filled($host) && filled($this->domain_kustom_link)
            && strtolower((string) $this->domain_kustom_link) === strtolower((string) $host);
    }

    public function tandaiDomainKustomTerhubung(string $host): void
    {
        if (! $this->cocokDomainKustomLink($host) || $this->domain_kustom_terhubung_pada !== null) {
            return;
        }

        $this->forceFill([
            'domain_kustom_terhubung_pada' => now(),
        ])->save();
    }

    public static function opsiTemaLink(): array
    {
        return [
            'sunset' => [
                'label' => 'Sunset Punch',
                'background' => 'radial-gradient(circle at top left, rgba(255, 183, 77, 0.32), transparent 28%), radial-gradient(circle at top right, rgba(255, 89, 94, 0.32), transparent 22%), linear-gradient(160deg, #1f0f19 0%, #0f172a 42%, #070c16 100%)',
                'surface' => 'rgba(17, 24, 39, 0.78)',
                'surface_soft' => 'rgba(255, 255, 255, 0.08)',
                'text' => '#fff6e9',
                'muted' => '#f3dfc8',
                'accent_soft' => '#ffd166',
                'border' => 'rgba(255, 255, 255, 0.12)',
                'button' => 'linear-gradient(135deg, #ff8a3d 0%, #ff5e5b 100%)',
            ],
            'mint' => [
                'label' => 'Mint Broadcast',
                'background' => 'radial-gradient(circle at top left, rgba(46, 204, 113, 0.2), transparent 30%), radial-gradient(circle at bottom right, rgba(52, 152, 219, 0.24), transparent 25%), linear-gradient(165deg, #062926 0%, #041c1a 48%, #031111 100%)',
                'surface' => 'rgba(6, 41, 38, 0.78)',
                'surface_soft' => 'rgba(255, 255, 255, 0.07)',
                'text' => '#eafff8',
                'muted' => '#c6f1e4',
                'accent_soft' => '#22d3ee',
                'border' => 'rgba(255, 255, 255, 0.11)',
                'button' => 'linear-gradient(135deg, #4ade80 0%, #22d3ee 100%)',
            ],
            'night' => [
                'label' => 'Night Studio',
                'background' => 'radial-gradient(circle at top left, rgba(125, 90, 255, 0.18), transparent 25%), radial-gradient(circle at bottom, rgba(255, 111, 145, 0.16), transparent 20%), linear-gradient(160deg, #111827 0%, #0b1220 48%, #020617 100%)',
                'surface' => 'rgba(15, 23, 42, 0.82)',
                'surface_soft' => 'rgba(255, 255, 255, 0.07)',
                'text' => '#eff4ff',
                'muted' => '#c9d6f5',
                'accent_soft' => '#fb7185',
                'border' => 'rgba(255, 255, 255, 0.12)',
                'button' => 'linear-gradient(135deg, #7c3aed 0%, #fb7185 100%)',
            ],
        ];
    }

    public static function normalisasiDomainKustomLink(?string $nilai): ?string
    {
        if (! filled($nilai)) {
            return null;
        }

        $kandidat = trim(strtolower((string) $nilai));

        if (! str_contains($kandidat, '://')) {
            $kandidat = 'http://'.$kandidat;
        }

        $host = parse_url($kandidat, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        return rtrim(strtolower($host), '.');
    }

    public static function normalisasiNomorWaLink(?string $nilai): ?string
    {
        if (! filled($nilai)) {
            return null;
        }

        $nomor = preg_replace('/[^\d+]+/', '', trim((string) $nilai));

        if (! is_string($nomor) || $nomor === '') {
            return null;
        }

        if (Str::startsWith($nomor, '+')) {
            $nomor = ltrim($nomor, '+');
        }

        if (Str::startsWith($nomor, '0')) {
            $nomor = '62'.Str::after($nomor, '0');
        }

        if (! preg_match('/^62\d{8,15}$/', $nomor)) {
            return null;
        }

        return $nomor;
    }

    public static function apakahDomainKustomLinkValid(?string $host): bool
    {
        if (! filled($host)) {
            return false;
        }

        return (bool) preg_match(
            '/^(?=.{3,120}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
            (string) $host
        );
    }

    public static function hostAplikasiLinkPublik(): array
    {
        return collect(config('link_publik.host_aplikasi', []))
            ->filter()
            ->map(fn ($host) => strtolower((string) $host))
            ->unique()
            ->values()
            ->all();
    }

    public static function adalahHostAplikasiLinkPublik(?string $host): bool
    {
        return filled($host) && in_array(strtolower((string) $host), self::hostAplikasiLinkPublik(), true);
    }

    public static function resolusikanDariDomainKustomLink(?string $host): ?self
    {
        $host = self::normalisasiDomainKustomLink($host);

        if (! filled($host) || self::adalahHostAplikasiLinkPublik($host)) {
            return null;
        }

        return self::query()->where('domain_kustom_link', $host)->first();
    }
}
