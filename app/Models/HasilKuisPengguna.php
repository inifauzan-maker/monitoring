<?php

namespace App\Models;

use Database\Factories\HasilKuisPenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilKuisPengguna extends Model
{
    /** @use HasFactory<HasilKuisPenggunaFactory> */
    use HasFactory;

    protected $table = 'hasil_kuis_pengguna';

    protected $fillable = [
        'kuis_lms_id',
        'user_id',
        'jawaban_pengguna',
        'jumlah_benar',
        'jumlah_pertanyaan',
        'skor',
        'lulus',
        'jumlah_percobaan',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'kuis_lms_id' => 'integer',
            'user_id' => 'integer',
            'jawaban_pengguna' => 'array',
            'jumlah_benar' => 'integer',
            'jumlah_pertanyaan' => 'integer',
            'skor' => 'integer',
            'lulus' => 'boolean',
            'jumlah_percobaan' => 'integer',
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
