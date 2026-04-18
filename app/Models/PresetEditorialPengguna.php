<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresetEditorialPengguna extends Model
{
    protected $table = 'preset_editorial_pengguna';

    protected $fillable = [
        'user_id',
        'nama_preset',
        'konfigurasi_filter',
    ];

    protected function casts(): array
    {
        return [
            'konfigurasi_filter' => 'array',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
