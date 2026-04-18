<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadTindakLanjut extends Model
{
    use HasFactory;

    public const STATUSES = [
        'direncanakan' => 'Direncanakan',
        'selesai' => 'Selesai',
    ];

    protected $table = 'lead_tindak_lanjut';

    protected $fillable = [
        'lead_id',
        'user_id',
        'catatan',
        'jadwal_tindak_lanjut',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jadwal_tindak_lanjut' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return self::STATUSES;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
