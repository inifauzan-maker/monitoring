<?php

namespace App\Models;

use Database\Factories\ProgresBelajarMateriFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresBelajarMateri extends Model
{
    /** @use HasFactory<ProgresBelajarMateriFactory> */
    use HasFactory;

    protected $table = 'progres_belajar_materi';

    protected $fillable = [
        'user_id',
        'materi_kursus_id',
        'detik_terakhir',
        'persen_progres',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'materi_kursus_id' => 'integer',
            'detik_terakhir' => 'integer',
            'persen_progres' => 'integer',
            'selesai_pada' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function materiKursus(): BelongsTo
    {
        return $this->belongsTo(MateriKursus::class, 'materi_kursus_id');
    }

    public function sudahSelesai(): bool
    {
        return $this->selesai_pada !== null || (int) $this->persen_progres >= 100;
    }

    public function sedangBerjalan(): bool
    {
        return ! $this->sudahSelesai() && (int) $this->persen_progres > 0;
    }

    public function labelStatus(): string
    {
        if ($this->sudahSelesai()) {
            return 'Selesai';
        }

        if ($this->sedangBerjalan()) {
            return 'Berjalan';
        }

        return 'Belum Mulai';
    }

    public function kelasBadgeStatus(): string
    {
        if ($this->sudahSelesai()) {
            return 'bg-green-lt text-green';
        }

        if ($this->sedangBerjalan()) {
            return 'bg-yellow-lt text-yellow';
        }

        return 'bg-secondary-lt text-secondary';
    }
}
