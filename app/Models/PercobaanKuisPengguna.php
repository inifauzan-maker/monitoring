<?php

namespace App\Models;

use Database\Factories\PercobaanKuisPenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PercobaanKuisPengguna extends Model
{
    /** @use HasFactory<PercobaanKuisPenggunaFactory> */
    use HasFactory;

    protected $table = 'percobaan_kuis_pengguna';

    protected $fillable = [
        'kuis_lms_id',
        'user_id',
        'percobaan_ke',
        'paket_soal',
        'jawaban_pengguna',
        'jumlah_benar',
        'jumlah_pertanyaan',
        'skor',
        'lulus',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'kuis_lms_id' => 'integer',
            'user_id' => 'integer',
            'percobaan_ke' => 'integer',
            'paket_soal' => 'array',
            'jawaban_pengguna' => 'array',
            'jumlah_benar' => 'integer',
            'jumlah_pertanyaan' => 'integer',
            'skor' => 'integer',
            'lulus' => 'boolean',
            'selesai_pada' => 'datetime',
        ];
    }

    public function kuisLms(): BelongsTo
    {
        return $this->belongsTo(KuisLms::class, 'kuis_lms_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function labelHasil(): string
    {
        return $this->lulus ? 'Lulus' : 'Belum Lulus';
    }

    public function kelasBadgeHasil(): string
    {
        return $this->lulus
            ? 'bg-green-lt text-green'
            : 'bg-red-lt text-red';
    }
}
