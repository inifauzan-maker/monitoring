<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasLinkPublik extends Model
{
    protected $table = 'aktivitas_link_publik';

    protected $fillable = [
        'user_id',
        'link_pengguna_id',
        'jenis_aktivitas',
        'session_id',
        'ip_address',
        'user_agent',
        'referrer',
        'sumber_traffic',
        'url_tujuan',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linkPengguna(): BelongsTo
    {
        return $this->belongsTo(LinkPengguna::class, 'link_pengguna_id');
    }
}
