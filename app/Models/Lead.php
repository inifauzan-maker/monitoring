<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const CHANNELS = [
        'WhatsApp' => 'WhatsApp',
        'Instagram' => 'Instagram',
        'TikTok' => 'TikTok',
        'Facebook' => 'Facebook',
        'Website' => 'Website',
        'Referensi' => 'Referensi',
        'Ads/Iklan' => 'Ads/Iklan',
    ];

    public const STATUSES = [
        'prospek' => 'Prospek',
        'follow_up' => 'Tindak Lanjut',
        'closing' => 'Berhasil',
        'batal' => 'Batal',
    ];

    protected $table = 'leads';

    protected $fillable = [
        'created_by',
        'pic_id',
        'nama_siswa',
        'asal_sekolah',
        'nomor_telepon',
        'channel',
        'sumber',
        'status',
        'jadwal_tindak_lanjut',
        'catatan',
        'kontak_terakhir',
    ];

    protected function casts(): array
    {
        return [
            'jadwal_tindak_lanjut' => 'datetime',
            'kontak_terakhir' => 'datetime',
        ];
    }

    public static function channelOptions(): array
    {
        return self::CHANNELS;
    }

    public static function statusOptions(): array
    {
        return self::STATUSES;
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(LeadTindakLanjut::class, 'lead_id');
    }

    public function labelStatus(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function kelasBadgeStatus(): string
    {
        return match ($this->status) {
            'prospek' => 'bg-primary-lt text-primary',
            'follow_up' => 'bg-orange-lt text-orange',
            'closing' => 'bg-green-lt text-green',
            'batal' => 'bg-secondary-lt text-secondary',
            default => 'bg-secondary-lt text-secondary',
        };
    }
}
