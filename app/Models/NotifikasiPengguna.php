<?php

namespace App\Models;

use Database\Factories\NotifikasiPenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiPengguna extends Model
{
    /** @use HasFactory<NotifikasiPenggunaFactory> */
    use HasFactory;

    public const OPSI_TIPE = [
        'info' => 'Info',
        'success' => 'Berhasil',
        'warning' => 'Perhatian',
        'danger' => 'Penting',
    ];

    protected $table = 'notifikasi_pengguna';

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'tautan',
        'metadata',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'dibaca_pada' => 'datetime',
        ];
    }

    public static function opsiTipe(): array
    {
        return self::OPSI_TIPE;
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sudahDibaca(): bool
    {
        return $this->dibaca_pada !== null;
    }

    public function tandaiSudahDibaca(): void
    {
        if ($this->sudahDibaca()) {
            return;
        }

        $this->forceFill([
            'dibaca_pada' => now(),
        ])->save();
    }

    public function labelTipe(): string
    {
        return self::OPSI_TIPE[$this->tipe] ?? ucfirst((string) $this->tipe);
    }

    public function kelasBadgeTipe(): string
    {
        return match ($this->tipe) {
            'success' => 'bg-green-lt text-green',
            'warning' => 'bg-yellow-lt text-yellow',
            'danger' => 'bg-red-lt text-red',
            default => 'bg-blue-lt text-blue',
        };
    }

    public function kelasIkonTipe(): string
    {
        return match ($this->tipe) {
            'success' => 'text-green',
            'warning' => 'text-yellow',
            'danger' => 'text-red',
            default => 'text-blue',
        };
    }
}
