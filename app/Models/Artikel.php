<?php

namespace App\Models;

use Database\Factories\ArtikelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Artikel extends Model
{
    /** @use HasFactory<ArtikelFactory> */
    use HasFactory;

    public const TINGKAT_KEAHLIAN = [
        'pemula' => 'Pemula',
        'menengah' => 'Menengah',
        'ahli' => 'Ahli',
    ];

    public const CHECKLIST_KESIAPAN = [
        'keyword_sudah_dicek' => 'Keyword utama sudah dicek penempatannya.',
        'metadata_seo_final' => 'Judul SEO dan deskripsi SEO sudah final.',
        'referensi_sudah_valid' => 'Sumber referensi sudah valid dan relevan.',
        'konten_sudah_dicek' => 'Konten sudah diperiksa ulang dan bebas typo utama.',
        'gambar_unggulan_siap' => 'Gambar unggulan siap dipakai dan relevan.',
    ];

    protected $table = 'artikel';

    protected $fillable = [
        'judul',
        'slug',
        'kata_kunci_utama',
        'ringkasan',
        'konten',
        'kategori_artikel_id',
        'penulis_id',
        'tingkat_keahlian',
        'bio_penulis',
        'sumber_referensi',
        'judul_seo',
        'deskripsi_seo',
        'outline_seo',
        'checklist_kesiapan',
        'gambar_unggulan_path',
        'alt_gambar_unggulan',
        'sudah_diterbitkan',
        'diterbitkan_pada',
    ];

    protected function casts(): array
    {
        return [
            'sumber_referensi' => 'array',
            'checklist_kesiapan' => 'array',
            'sudah_diterbitkan' => 'boolean',
            'diterbitkan_pada' => 'datetime',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, 'kategori_artikel_id');
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    public function revisi(): HasMany
    {
        return $this->hasMany(ArtikelRevisi::class, 'artikel_id')->latest();
    }

    public function scopeDiterbitkan(Builder $query): Builder
    {
        return $query->terbitAktif();
    }

    public function scopeTerbitAktif(Builder $query): Builder
    {
        return $query
            ->where('sudah_diterbitkan', true)
            ->whereNotNull('diterbitkan_pada')
            ->where('diterbitkan_pada', '<=', now());
    }

    public function scopeTerjadwal(Builder $query): Builder
    {
        return $query
            ->where('sudah_diterbitkan', true)
            ->whereNotNull('diterbitkan_pada')
            ->where('diterbitkan_pada', '>', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('sudah_diterbitkan', false);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function labelTingkatKeahlian(): string
    {
        return self::TINGKAT_KEAHLIAN[$this->tingkat_keahlian] ?? ucfirst((string) $this->tingkat_keahlian);
    }

    public function labelStatusPublikasi(): string
    {
        return match (true) {
            $this->sedangTerjadwal() => 'Terjadwal',
            $this->sudahTerbitAktif() => 'Diterbitkan',
            default => 'Draft',
        };
    }

    public function kelasBadgePublikasi(): string
    {
        return match (true) {
            $this->sedangTerjadwal() => 'bg-blue-lt text-blue',
            $this->sudahTerbitAktif() => 'bg-green-lt text-green',
            default => 'bg-orange-lt text-orange',
        };
    }

    public function adalahDraft(): bool
    {
        return ! $this->sudah_diterbitkan;
    }

    public function sedangTerjadwal(): bool
    {
        return $this->sudah_diterbitkan
            && $this->diterbitkan_pada !== null
            && $this->diterbitkan_pada->isFuture();
    }

    public function sudahTerbitAktif(): bool
    {
        return $this->sudah_diterbitkan
            && $this->diterbitkan_pada !== null
            && ! $this->diterbitkan_pada->isFuture();
    }

    public function getUrlGambarUnggulanAttribute(): ?string
    {
        if (! $this->gambar_unggulan_path) {
            return null;
        }

        return Storage::disk('public')->url($this->gambar_unggulan_path);
    }

    public function snapshotRevisi(): array
    {
        return [
            'judul' => $this->judul,
            'slug' => $this->slug,
            'kata_kunci_utama' => $this->kata_kunci_utama,
            'ringkasan' => $this->ringkasan,
            'konten' => $this->konten,
            'kategori_artikel_id' => $this->kategori_artikel_id,
            'tingkat_keahlian' => $this->tingkat_keahlian,
            'bio_penulis' => $this->bio_penulis,
            'sumber_referensi' => $this->sumber_referensi,
            'judul_seo' => $this->judul_seo,
            'deskripsi_seo' => $this->deskripsi_seo,
            'outline_seo' => $this->outline_seo,
            'checklist_kesiapan' => self::normalisasiChecklistKesiapan($this->checklist_kesiapan),
            'alt_gambar_unggulan' => $this->alt_gambar_unggulan,
        ];
    }

    public static function labelChecklistKesiapan(): array
    {
        return self::CHECKLIST_KESIAPAN;
    }

    public static function normalisasiChecklistKesiapan(?array $input = null): array
    {
        $input ??= [];
        $normal = [];

        foreach (self::CHECKLIST_KESIAPAN as $key => $label) {
            $value = $input[$key] ?? null;

            $normal[$key] = in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
        }

        return $normal;
    }

    public function statusChecklistKesiapan(): array
    {
        return self::normalisasiChecklistKesiapan($this->checklist_kesiapan);
    }

    public function checklistKesiapanLengkap(): bool
    {
        return ! in_array(false, $this->statusChecklistKesiapan(), true);
    }

    public function evaluasiKesiapan(): array
    {
        $keyword = trim((string) $this->kata_kunci_utama);
        $judul = trim((string) $this->judul);
        $slug = trim((string) $this->slug);
        $ringkasanPanjang = mb_strlen(trim((string) $this->ringkasan));
        $judulSeoPanjang = mb_strlen(trim((string) $this->judul_seo));
        $deskripsiSeoPanjang = mb_strlen(trim((string) $this->deskripsi_seo));
        $jumlahSumber = count(array_filter($this->sumber_referensi ?? []));
        $jumlahKataKonten = str_word_count(strip_tags((string) $this->konten));
        $checklistLengkap = $this->checklistKesiapanLengkap();
        $keywordNormalized = str((string) $keyword)->lower()->slug(' ');
        $judulNormalized = str((string) $judul)->lower()->slug(' ');
        $slugNormalized = str((string) $slug)->lower()->replace('-', ' ');

        $checks = [
            [
                'judul' => 'Keyword utama tersedia',
                'ok' => $keyword !== '',
                'bobot' => 10,
                'blokir_publikasi' => true,
                'pesan' => $keyword !== '' ? 'Keyword utama sudah diisi.' : 'Isi kata kunci utama terlebih dahulu.',
            ],
            [
                'judul' => 'Keyword ada di judul',
                'ok' => $keyword !== '' && str_contains((string) $judulNormalized, (string) $keywordNormalized),
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $keyword !== '' && str_contains((string) $judulNormalized, (string) $keywordNormalized)
                    ? 'Judul sudah memuat keyword utama.'
                    : 'Masukkan keyword utama ke judul.',
            ],
            [
                'judul' => 'Slug relevan dengan keyword',
                'ok' => $keyword !== '' && str_contains((string) $slugNormalized, (string) $keywordNormalized),
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $keyword !== '' && str_contains((string) $slugNormalized, (string) $keywordNormalized)
                    ? 'Slug sudah selaras dengan keyword.'
                    : 'Selaraskan slug dengan keyword utama.',
            ],
            [
                'judul' => 'Ringkasan berada di rentang ideal',
                'ok' => $ringkasanPanjang >= 120 && $ringkasanPanjang <= 200,
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $ringkasanPanjang >= 120 && $ringkasanPanjang <= 200
                    ? 'Ringkasan sudah berada di rentang ideal.'
                    : 'Buat ringkasan 120-200 karakter.',
            ],
            [
                'judul' => 'Metadata SEO lengkap',
                'ok' => filled($this->judul_seo) && filled($this->deskripsi_seo),
                'bobot' => 10,
                'blokir_publikasi' => true,
                'pesan' => filled($this->judul_seo) && filled($this->deskripsi_seo)
                    ? 'Judul SEO dan deskripsi SEO sudah terisi.'
                    : 'Lengkapi judul SEO dan deskripsi SEO.',
            ],
            [
                'judul' => 'Panjang metadata SEO sesuai',
                'ok' => $judulSeoPanjang >= 30 && $judulSeoPanjang <= 60 && $deskripsiSeoPanjang >= 120 && $deskripsiSeoPanjang <= 160,
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $judulSeoPanjang >= 30 && $judulSeoPanjang <= 60 && $deskripsiSeoPanjang >= 120 && $deskripsiSeoPanjang <= 160
                    ? 'Panjang metadata SEO sudah sesuai.'
                    : 'Judul SEO ideal 30-60 karakter dan deskripsi SEO 120-160 karakter.',
            ],
            [
                'judul' => 'Outline SEO tersedia',
                'ok' => filled($this->outline_seo),
                'bobot' => 10,
                'blokir_publikasi' => true,
                'pesan' => filled($this->outline_seo)
                    ? 'Outline SEO sudah tersedia.'
                    : 'Isi outline SEO terlebih dahulu.',
            ],
            [
                'judul' => 'Referensi tersedia',
                'ok' => $jumlahSumber > 0,
                'bobot' => 10,
                'blokir_publikasi' => true,
                'pesan' => $jumlahSumber > 0
                    ? $jumlahSumber.' sumber referensi sudah ditambahkan.'
                    : 'Tambahkan minimal satu sumber referensi.',
            ],
            [
                'judul' => 'Konten cukup panjang',
                'ok' => $jumlahKataKonten >= 300,
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $jumlahKataKonten >= 300
                    ? 'Konten memiliki '.$jumlahKataKonten.' kata.'
                    : 'Konten masih '.$jumlahKataKonten.' kata. Target minimal 300 kata.',
            ],
            [
                'judul' => 'Checklist editorial lengkap',
                'ok' => $checklistLengkap,
                'bobot' => 10,
                'blokir_publikasi' => true,
                'pesan' => $checklistLengkap
                    ? 'Checklist editorial sudah lengkap.'
                    : 'Lengkapi checklist kesiapan editorial.',
            ],
        ];

        $skor = collect($checks)->sum(fn (array $check) => $check['ok'] ? $check['bobot'] : 0);

        return [
            'skor' => $skor,
            'label' => match (true) {
                $skor >= 80 => 'Sangat Baik',
                $skor >= 60 => 'Cukup Baik',
                default => 'Perlu Revisi',
            },
            'checks' => $checks,
            'siap_terbit' => ! collect($checks)->contains(fn (array $check) => $check['blokir_publikasi'] && ! $check['ok']),
        ];
    }
}
