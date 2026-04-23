<?php

namespace App\Models;

use Database\Factories\MateriKursusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MateriKursus extends Model
{
    /** @use HasFactory<MateriKursusFactory> */
    use HasFactory;

    protected $table = 'materi_kursus';

    protected $fillable = [
        'kursus_id',
        'judul',
        'youtube_id',
        'durasi_detik',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'kursus_id' => 'integer',
            'durasi_detik' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function kursus(): BelongsTo
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    public function progresBelajar(): HasMany
    {
        return $this->hasMany(ProgresBelajarMateri::class, 'materi_kursus_id');
    }

    public function kuisLms(): HasMany
    {
        return $this->hasMany(KuisLms::class, 'materi_kursus_id')->latest();
    }

    public function labelDurasi(): string
    {
        $durasi = max(0, (int) $this->durasi_detik);
        $jam = intdiv($durasi, 3600);
        $menit = intdiv($durasi % 3600, 60);
        $detik = $durasi % 60;

        if ($jam > 0) {
            return sprintf('%02d:%02d:%02d', $jam, $menit, $detik);
        }

        return sprintf('%02d:%02d', $menit, $detik);
    }
}
