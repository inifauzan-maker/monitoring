<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatistikLinkHarian extends Model
{
    public const KUNJUNGAN_HALAMAN = 'kunjungan_halaman';
    public const KLIK_CTA = 'klik_cta';
    public const KLIK_LINK = 'klik_link';

    protected $table = 'statistik_link_harian';

    protected $fillable = [
        'user_id',
        'link_pengguna_id',
        'jenis_aktivitas',
        'tanggal',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linkPengguna(): BelongsTo
    {
        return $this->belongsTo(LinkPengguna::class, 'link_pengguna_id');
    }
}
