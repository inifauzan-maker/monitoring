<?php

namespace App\Models;

use Database\Factories\BankSoalLmsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankSoalLms extends Model
{
    /** @use HasFactory<BankSoalLmsFactory> */
    use HasFactory;

    public const OPSI_TINGKAT_KESULITAN = [
        'mudah' => 'Mudah',
        'menengah' => 'Menengah',
        'sulit' => 'Sulit',
    ];

    protected $table = 'bank_soal_lms';

    protected $fillable = [
        'kursus_id',
        'pertanyaan',
        'opsi_jawaban',
        'indeks_jawaban_benar',
        'tingkat_kesulitan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'kursus_id' => 'integer',
            'opsi_jawaban' => 'array',
            'indeks_jawaban_benar' => 'integer',
            'tingkat_kesulitan' => 'string',
            'aktif' => 'boolean',
        ];
    }

    public static function opsiTingkatKesulitan(): array
    {
        return self::OPSI_TINGKAT_KESULITAN;
    }

    public function kursus(): BelongsTo
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    public function kuisLms(): BelongsToMany
    {
        return $this->belongsToMany(KuisLms::class, 'bank_soal_kuis_lms', 'bank_soal_lms_id', 'kuis_lms_id')
            ->withPivot('urutan')
            ->withTimestamps()
            ->orderByPivot('urutan');
    }

    public function jawabanBenar(): ?string
    {
        return $this->opsi_jawaban[$this->indeks_jawaban_benar] ?? null;
    }

    public function labelTingkatKesulitan(): string
    {
        return self::OPSI_TINGKAT_KESULITAN[$this->tingkat_kesulitan] ?? 'Menengah';
    }

    public function kelasBadgeKesulitan(): string
    {
        return match ($this->tingkat_kesulitan) {
            'mudah' => 'bg-green-lt text-green',
            'sulit' => 'bg-red-lt text-red',
            default => 'bg-yellow-lt text-yellow',
        };
    }

    public function kelasBadgeAktif(): string
    {
        return $this->aktif
            ? 'bg-green-lt text-green'
            : 'bg-secondary-lt text-secondary';
    }
}
