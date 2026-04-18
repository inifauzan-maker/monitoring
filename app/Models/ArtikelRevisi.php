<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtikelRevisi extends Model
{
    protected $table = 'artikel_revisi';

    protected $fillable = [
        'artikel_id',
        'penulis_id',
        'tipe_pemicu',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function artikel(): BelongsTo
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    public function labelPemicu(): string
    {
        return match ($this->tipe_pemicu) {
            'awal' => 'Draft Awal',
            'manual' => 'Simpan Manual',
            'autosimpan' => 'Autosimpan',
            'sebelum_pulih' => 'Cadangan Sebelum Pulih',
            'dipulihkan' => 'Dipulihkan',
            'dijadwalkan' => 'Dijadwalkan',
            'diterbitkan' => 'Diterbitkan',
            'dibatalkan_terbit' => 'Dikembalikan ke Draft',
            default => 'Revisi',
        };
    }
}
