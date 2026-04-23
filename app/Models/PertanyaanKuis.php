<?php

namespace App\Models;

use Database\Factories\PertanyaanKuisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PertanyaanKuis extends Model
{
    /** @use HasFactory<PertanyaanKuisFactory> */
    use HasFactory;

    protected $table = 'pertanyaan_kuis';

    protected $fillable = [
        'kuis_lms_id',
        'pertanyaan',
        'opsi_jawaban',
        'indeks_jawaban_benar',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'kuis_lms_id' => 'integer',
            'opsi_jawaban' => 'array',
            'indeks_jawaban_benar' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function kuisLms(): BelongsTo
    {
        return $this->belongsTo(KuisLms::class, 'kuis_lms_id');
    }

    public function jawabanBenar(): ?string
    {
        return $this->opsi_jawaban[$this->indeks_jawaban_benar] ?? null;
    }
}
