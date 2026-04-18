<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LinkPengguna extends Model
{
    protected $table = 'link_pengguna';

    protected $fillable = [
        'user_id',
        'judul',
        'deskripsi',
        'url',
        'urutan',
        'aktif',
        'total_klik',
        'terakhir_diklik_pada',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'terakhir_diklik_pada' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aktivitasPublik(): HasMany
    {
        return $this->hasMany(AktivitasLinkPublik::class, 'link_pengguna_id');
    }

    public static function normalisasiUrlEksternal(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $normal = trim((string) $url);

        if ($normal === '') {
            return null;
        }

        if (Str::startsWith($normal, '//')) {
            $normal = 'https:'.$normal;
        }

        if (! preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $normal)) {
            $normal = 'https://'.$normal;
        }

        return $normal;
    }

    public static function apakahUrlEksternalValid(?string $url): bool
    {
        $normal = static::normalisasiUrlEksternal($url);

        if ($normal === null || ! filter_var($normal, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = Str::lower((string) parse_url($normal, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = Str::lower((string) parse_url($normal, PHP_URL_HOST));

        if ($host === '') {
            return false;
        }

        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        if (! Str::contains($host, '.')) {
            return false;
        }

        $labels = array_values(array_filter(explode('.', $host)));

        if (count($labels) < 2) {
            return false;
        }

        $tld = end($labels);

        return is_string($tld) && strlen($tld) >= 2;
    }

    public function urlTujuan(): string
    {
        return static::normalisasiUrlEksternal($this->url) ?? $this->url;
    }
}
