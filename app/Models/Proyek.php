<?php

namespace App\Models;

use Database\Factories\ProyekFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyek extends Model
{
    /** @use HasFactory<ProyekFactory> */
    use HasFactory;

    public const STATUS_PROJECT = [
        'perencanaan' => 'Perencanaan',
        'berjalan' => 'Berjalan',
        'tertunda' => 'Tertunda',
        'selesai' => 'Selesai',
    ];

    public const PRIORITAS_PROJECT = [
        'rendah' => 'Rendah',
        'sedang' => 'Sedang',
        'tinggi' => 'Tinggi',
        'kritis' => 'Kritis',
    ];

    protected $table = 'proyek';

    protected $fillable = [
        'kode_project',
        'nama_project',
        'klien',
        'status_project',
        'prioritas_project',
        'tanggal_mulai',
        'tanggal_target_selesai',
        'tanggal_selesai',
        'deskripsi_project',
        'alur_kerja',
        'sop_ringkas',
        'penanggung_jawab_id',
        'skor_evaluasi',
        'catatan_evaluasi',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_target_selesai' => 'date',
            'tanggal_selesai' => 'date',
            'skor_evaluasi' => 'integer',
        ];
    }

    public static function statusOptions(): array
    {
        return self::STATUS_PROJECT;
    }

    public static function prioritasOptions(): array
    {
        return self::PRIORITAS_PROJECT;
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(TugasProyek::class, 'proyek_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function labelStatusProject(): string
    {
        return self::STATUS_PROJECT[$this->status_project] ?? ucfirst(str_replace('_', ' ', (string) $this->status_project));
    }

    public function kelasBadgeStatusProject(): string
    {
        return match ($this->status_project) {
            'berjalan' => 'bg-blue-lt text-blue',
            'tertunda' => 'bg-yellow-lt text-yellow',
            'selesai' => 'bg-green-lt text-green',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function labelPrioritasProject(): string
    {
        return self::PRIORITAS_PROJECT[$this->prioritas_project] ?? ucfirst((string) $this->prioritas_project);
    }

    public function kelasBadgePrioritasProject(): string
    {
        return match ($this->prioritas_project) {
            'kritis' => 'bg-red-lt text-red',
            'tinggi' => 'bg-orange-lt text-orange',
            'sedang' => 'bg-blue-lt text-blue',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function persentaseProgres(): int
    {
        if (array_key_exists('rata_rata_progres', $this->attributes) && $this->attributes['rata_rata_progres'] !== null) {
            return (int) round((float) $this->attributes['rata_rata_progres']);
        }

        if (! $this->relationLoaded('tugas')) {
            return (int) round((float) $this->tugas()->avg('persentase_progres'));
        }

        return (int) round((float) $this->tugas->avg('persentase_progres'));
    }

    public function totalTugas(): int
    {
        if (array_key_exists('tugas_count', $this->attributes)) {
            return (int) $this->attributes['tugas_count'];
        }

        return $this->relationLoaded('tugas')
            ? $this->tugas->count()
            : $this->tugas()->count();
    }

    public function totalTugasSelesai(): int
    {
        if (array_key_exists('tugas_selesai_count', $this->attributes)) {
            return (int) $this->attributes['tugas_selesai_count'];
        }

        return $this->relationLoaded('tugas')
            ? $this->tugas->where('status_tugas', 'selesai')->count()
            : $this->tugas()->where('status_tugas', 'selesai')->count();
    }
}
