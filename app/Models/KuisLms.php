<?php

namespace App\Models;

use Database\Factories\KuisLmsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KuisLms extends Model
{
    /** @use HasFactory<KuisLmsFactory> */
    use HasFactory;

    protected $table = 'kuis_lms';

    protected $fillable = [
        'judul',
        'deskripsi',
        'target_tipe',
        'kursus_id',
        'materi_kursus_id',
        'nilai_lulus',
        'durasi_menit',
        'maksimal_percobaan',
        'gunakan_bank_soal',
        'acak_soal',
        'acak_opsi_jawaban',
        'jumlah_soal_tampil',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'kursus_id' => 'integer',
            'materi_kursus_id' => 'integer',
            'nilai_lulus' => 'integer',
            'durasi_menit' => 'integer',
            'maksimal_percobaan' => 'integer',
            'gunakan_bank_soal' => 'boolean',
            'acak_soal' => 'boolean',
            'acak_opsi_jawaban' => 'boolean',
            'jumlah_soal_tampil' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function kursus(): BelongsTo
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    public function materiKursus(): BelongsTo
    {
        return $this->belongsTo(MateriKursus::class, 'materi_kursus_id');
    }

    public function pertanyaan(): HasMany
    {
        return $this->hasMany(PertanyaanKuis::class, 'kuis_lms_id')->orderBy('urutan');
    }

    public function hasilPengguna(): HasMany
    {
        return $this->hasMany(HasilKuisPengguna::class, 'kuis_lms_id');
    }

    public function percobaanPengguna(): HasMany
    {
        return $this->hasMany(PercobaanKuisPengguna::class, 'kuis_lms_id')->latest('percobaan_ke');
    }

    public function bankSoal(): BelongsToMany
    {
        return $this->belongsToMany(BankSoalLms::class, 'bank_soal_kuis_lms', 'kuis_lms_id', 'bank_soal_lms_id')
            ->withPivot('urutan')
            ->withTimestamps()
            ->orderByPivot('urutan');
    }

    public function untukKursus(): bool
    {
        return $this->target_tipe === 'kursus';
    }

    public function untukMateri(): bool
    {
        return $this->target_tipe === 'materi';
    }

    public function menggunakanBankSoal(): bool
    {
        return $this->gunakan_bank_soal;
    }

    public function mengacakSoal(): bool
    {
        return $this->acak_soal;
    }

    public function mengacakOpsiJawaban(): bool
    {
        return $this->acak_opsi_jawaban;
    }

    public function menggunakanTimer(): bool
    {
        return (int) ($this->durasi_menit ?? 0) > 0;
    }

    public function durasiTimerDetik(): int
    {
        return max(0, (int) ($this->durasi_menit ?? 0) * 60);
    }

    public function labelDurasiTimer(): string
    {
        if (! $this->menggunakanTimer()) {
            return 'Tanpa timer';
        }

        return (int) $this->durasi_menit.' menit';
    }

    public function membatasiPercobaan(): bool
    {
        return (int) ($this->maksimal_percobaan ?? 0) > 0;
    }

    public function labelMaksimalPercobaan(): string
    {
        if (! $this->membatasiPercobaan()) {
            return 'Percobaan fleksibel';
        }

        $jumlah = (int) $this->maksimal_percobaan;

        return $jumlah.' percobaan';
    }

    public function labelTarget(): string
    {
        return $this->untukMateri() ? 'Materi' : 'Kursus';
    }

    public function labelSumberSoal(): string
    {
        return $this->menggunakanBankSoal() ? 'Bank Soal' : 'Manual';
    }

    public function namaTarget(): string
    {
        return $this->untukMateri()
            ? ($this->materiKursus?->judul ?? '-')
            : ($this->kursus?->judul ?? '-');
    }

    public function jumlahSoalSumber(): int
    {
        if ($this->menggunakanBankSoal()) {
            if (array_key_exists('bank_soal_count', $this->attributes)) {
                return (int) $this->attributes['bank_soal_count'];
            }

            if ($this->relationLoaded('bankSoal')) {
                return $this->bankSoal->count();
            }

            return 0;
        }

        if (array_key_exists('pertanyaan_count', $this->attributes)) {
            return (int) $this->attributes['pertanyaan_count'];
        }

        if ($this->relationLoaded('pertanyaan')) {
            return $this->pertanyaan->count();
        }

        return 0;
    }

    public function jumlahSoalTampilEfektif(): int
    {
        $jumlahSumber = $this->jumlahSoalSumber();

        if ($jumlahSumber === 0) {
            return 0;
        }

        if (! $this->jumlah_soal_tampil || $this->jumlah_soal_tampil < 1) {
            return $jumlahSumber;
        }

        return min($jumlahSumber, (int) $this->jumlah_soal_tampil);
    }

    public function kelasBadgeAktif(): string
    {
        return $this->aktif
            ? 'bg-green-lt text-green'
            : 'bg-secondary-lt text-secondary';
    }
}
