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
        'keyword_turunan',
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
            'keyword_turunan' => $this->keyword_turunan,
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

    public function daftarKeywordTurunan(): array
    {
        return collect(preg_split('/\r?\n|,/', (string) $this->keyword_turunan) ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function intentPencarianDariTeks(string $teks): array
    {
        $gabungan = strtolower(trim($teks));

        $peta = [
            [
                'kode' => 'transaksional',
                'label' => 'Transaksional',
                'regex' => '/\b(daftar|beli|booking|hubungi|konsultasi|jasa|layanan|paket|promo|diskon|harga|biaya)\b/',
                'alasan' => 'Teks mengarah ke aksi langsung, penawaran, atau intent closing.',
            ],
            [
                'kode' => 'komersial',
                'label' => 'Komersial',
                'regex' => '/\b(terbaik|review|vs|perbandingan|rekomendasi|pilihan)\b/',
                'alasan' => 'Teks menunjukkan pembaca sedang membandingkan opsi sebelum memutuskan.',
            ],
            [
                'kode' => 'navigasional',
                'label' => 'Navigasional',
                'regex' => '/\b(login|masuk|kontak|alamat|website resmi|official)\b/',
                'alasan' => 'Teks terlihat seperti pencarian menuju brand, halaman, atau lokasi tertentu.',
            ],
            [
                'kode' => 'informasional',
                'label' => 'Informasional',
                'regex' => '/\b(cara|apa itu|panduan|tutorial|contoh|tips|strategi|langkah)\b/',
                'alasan' => 'Teks menunjukkan intent belajar atau mencari penjelasan.',
            ],
        ];

        foreach ($peta as $intent) {
            if (preg_match($intent['regex'], $gabungan) === 1) {
                return [
                    'kode' => $intent['kode'],
                    'label' => $intent['label'],
                    'alasan' => $intent['alasan'],
                ];
            }
        }

        return [
            'kode' => 'belum-terbaca',
            'label' => 'Belum Terbaca',
            'alasan' => 'Teks masih terlalu umum untuk dipetakan ke intent pencarian tertentu.',
        ];
    }

    public function bagianOutline(): array
    {
        $baris = preg_split('/\r?\n/', (string) $this->outline_seo) ?: [];
        $bagian = [];
        $aktif = null;

        $simpanAktif = function () use (&$aktif, &$bagian): void {
            if (! $aktif || blank($aktif['judul'])) {
                return;
            }

            $aktif['isi'] = array_values(array_filter(
                array_map(fn ($item) => trim((string) $item), $aktif['isi']),
                fn ($item) => $item !== ''
            ));

            $bagian[] = $aktif;
            $aktif = null;
        };

        foreach ($baris as $barisItem) {
            $nilai = trim((string) $barisItem);

            if ($nilai === '') {
                continue;
            }

            if (preg_match('/^(#{2,6})\s+(.+)$/', $nilai, $cocok) === 1) {
                $simpanAktif();

                $aktif = [
                    'level' => strlen($cocok[1]),
                    'judul' => trim($cocok[2]),
                    'isi' => [],
                ];

                continue;
            }

            if ($aktif !== null) {
                $aktif['isi'][] = preg_replace('/^[-*]\s+/', '', $nilai) ?? $nilai;
            }
        }

        $simpanAktif();

        return $bagian;
    }

    public function faqDariOutline(): array
    {
        $faq = [];
        $aktif = null;

        $simpanAktif = function () use (&$aktif, &$faq): void {
            if (! $aktif) {
                return;
            }

            $jawaban = collect($aktif['jawaban'])
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->implode(' ');

            if ($aktif['pertanyaan'] !== '' && $jawaban !== '') {
                $faq[] = [
                    'pertanyaan' => $aktif['pertanyaan'],
                    'jawaban' => $jawaban,
                ];
            }

            $aktif = null;
        };

        foreach ($this->bagianOutline() as $bagian) {
            $judul = trim((string) ($bagian['judul'] ?? ''));

            if (! str_ends_with($judul, '?')) {
                $simpanAktif();
                continue;
            }

            $simpanAktif();
            $aktif = [
                'pertanyaan' => $judul,
                'jawaban' => $bagian['isi'] ?? [],
            ];
        }

        $simpanAktif();

        return $faq;
    }

    public function evaluasiIntentPerBagian(): array
    {
        $bagian = $this->bagianOutline();
        $hasil = collect($bagian)->map(function (array $item): array {
            $teks = trim(($item['judul'] ?? '').' '.implode(' ', $item['isi'] ?? []));
            $intent = $this->intentPencarianDariTeks($teks);

            return [
                'judul' => $item['judul'] ?? '',
                'intent' => $intent,
            ];
        })->filter(fn (array $item) => $item['judul'] !== '')->values();

        $terbaca = $hasil->where('intent.kode', '!=', 'belum-terbaca')->count();
        $total = max($hasil->count(), 1);
        $persentase = (int) round(($terbaca / $total) * 100);

        return [
            'bagian' => $hasil->all(),
            'persentase' => $hasil->isEmpty() ? 0 : $persentase,
            'cukup_terbaca' => $hasil->count() >= 2 && $persentase >= 60,
        ];
    }

    public function evaluasiSchemaReadiness(): array
    {
        $faq = $this->faqDariOutline();
        $bagian = $this->bagianOutline();
        $checks = [
            [
                'label' => 'Article metadata inti',
                'ok' => filled($this->judul_seo) && filled($this->deskripsi_seo) && filled($this->ringkasan),
                'detail' => 'Periksa judul SEO, deskripsi SEO, dan ringkasan sebagai fondasi Article schema.',
            ],
            [
                'label' => 'Author context tersedia',
                'ok' => filled($this->bio_penulis),
                'detail' => 'Bio penulis membantu memperkuat identitas author pada metadata artikel.',
            ],
            [
                'label' => 'FAQ schema siap',
                'ok' => count($faq) >= 2,
                'detail' => 'Minimal 2 FAQ lengkap dibutuhkan agar schema FAQ layak dipasang.',
            ],
            [
                'label' => 'Media context tersedia',
                'ok' => filled($this->alt_gambar_unggulan),
                'detail' => 'Alt gambar membantu memberi konteks visual untuk metadata artikel.',
            ],
            [
                'label' => 'Struktur article body siap',
                'ok' => count($bagian) >= 3,
                'detail' => 'Minimal 3 section outline membantu struktur Article schema lebih utuh.',
            ],
        ];

        $lolos = collect($checks)->where('ok', true)->count();
        $total = max(count($checks), 1);
        $persentase = (int) round(($lolos / $total) * 100);

        return [
            'checks' => $checks,
            'lolos' => $lolos,
            'total' => $total,
            'persentase' => $persentase,
            'cukup_siap' => $persentase >= 80,
        ];
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
        $schemaReadiness = $this->evaluasiSchemaReadiness();
        $intentPerBagian = $this->evaluasiIntentPerBagian();

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
            [
                'judul' => 'Schema readiness cukup kuat',
                'ok' => $schemaReadiness['cukup_siap'],
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $schemaReadiness['cukup_siap']
                    ? 'Skor schema '.$schemaReadiness['persentase'].'% dengan '.$schemaReadiness['lolos'].' dari '.$schemaReadiness['total'].' komponen siap.'
                    : 'Skor schema baru '.$schemaReadiness['persentase'].'%. Lengkapi metadata, FAQ, media, dan struktur section.',
            ],
            [
                'judul' => 'Intent per section outline terbaca',
                'ok' => $intentPerBagian['cukup_terbaca'],
                'bobot' => 10,
                'blokir_publikasi' => false,
                'pesan' => $intentPerBagian['cukup_terbaca']
                    ? 'Intent terbaca pada mayoritas section outline.'
                    : 'Perjelas judul section agar intent per bagian lebih mudah terbaca.',
            ],
        ];

        $totalBobot = collect($checks)->sum('bobot');
        $nilaiMentah = collect($checks)->sum(fn (array $check) => $check['ok'] ? $check['bobot'] : 0);
        $skor = (int) round(($nilaiMentah / max($totalBobot, 1)) * 100);

        return [
            'skor' => $skor,
            'label' => match (true) {
                $skor >= 80 => 'Sangat Baik',
                $skor >= 60 => 'Cukup Baik',
                default => 'Perlu Revisi',
            },
            'checks' => $checks,
            'schema_readiness' => $schemaReadiness,
            'intent_per_bagian' => $intentPerBagian,
            'siap_terbit' => ! collect($checks)->contains(fn (array $check) => $check['blokir_publikasi'] && ! $check['ok']),
        ];
    }
}
