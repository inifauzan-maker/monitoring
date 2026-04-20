<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriProgresTugas extends Model
{
    protected $table = 'histori_progres_tugas';

    protected $fillable = [
        'tugas_proyek_id',
        'user_id',
        'status_sebelum',
        'status_sesudah',
        'progres_sebelum',
        'progres_sesudah',
        'catatan_histori',
    ];

    protected function casts(): array
    {
        return [
            'progres_sebelum' => 'integer',
            'progres_sesudah' => 'integer',
        ];
    }

    public function tugasProyek(): BelongsTo
    {
        return $this->belongsTo(TugasProyek::class, 'tugas_proyek_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function labelStatusSebelum(): string
    {
        return TugasProyek::statusOptions()[$this->status_sebelum] ?? ($this->status_sebelum ?: '-');
    }

    public function labelStatusSesudah(): string
    {
        return TugasProyek::statusOptions()[$this->status_sesudah] ?? ($this->status_sesudah ?: '-');
    }
}
