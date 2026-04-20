<?php

namespace App\Models;

use Database\Factories\TugasProyekFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TugasProyek extends Model
{
    /** @use HasFactory<TugasProyekFactory> */
    use HasFactory;

    public const STATUS_TUGAS = [
        'belum_mulai' => 'Belum Mulai',
        'berjalan' => 'Berjalan',
        'review' => 'Review',
        'selesai' => 'Selesai',
        'tertunda' => 'Tertunda',
    ];

    public const PRIORITAS_TUGAS = [
        'rendah' => 'Rendah',
        'sedang' => 'Sedang',
        'tinggi' => 'Tinggi',
        'kritis' => 'Kritis',
    ];

    protected $table = 'tugas_proyek';

    protected $fillable = [
        'proyek_id',
        'judul_tugas',
        'deskripsi_tugas',
        'status_tugas',
        'prioritas_tugas',
        'persentase_progres',
        'penanggung_jawab_id',
        'tanggal_mulai',
        'tanggal_target',
        'tanggal_selesai',
        'catatan_tugas',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_target' => 'date',
            'tanggal_selesai' => 'date',
            'persentase_progres' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public static function statusOptions(): array
    {
        return self::STATUS_TUGAS;
    }

    public static function prioritasOptions(): array
    {
        return self::PRIORITAS_TUGAS;
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function historiProgres(): HasMany
    {
        return $this->hasMany(HistoriProgresTugas::class, 'tugas_proyek_id');
    }

    public function labelStatusTugas(): string
    {
        return self::STATUS_TUGAS[$this->status_tugas] ?? ucfirst(str_replace('_', ' ', (string) $this->status_tugas));
    }

    public function kelasBadgeStatusTugas(): string
    {
        return match ($this->status_tugas) {
            'berjalan' => 'bg-blue-lt text-blue',
            'review' => 'bg-indigo-lt text-indigo',
            'selesai' => 'bg-green-lt text-green',
            'tertunda' => 'bg-yellow-lt text-yellow',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function labelPrioritasTugas(): string
    {
        return self::PRIORITAS_TUGAS[$this->prioritas_tugas] ?? ucfirst((string) $this->prioritas_tugas);
    }

    public function kelasBadgePrioritasTugas(): string
    {
        return match ($this->prioritas_tugas) {
            'kritis' => 'bg-red-lt text-red',
            'tinggi' => 'bg-orange-lt text-orange',
            'sedang' => 'bg-blue-lt text-blue',
            default => 'bg-secondary-lt text-secondary',
        };
    }
}
