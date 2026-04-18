<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukItem extends Model
{
    use HasFactory;

    public const PROGRAMS = [
        'senirupa' => 'Bimbel Gambar Senirupa Desain',
        'arsitektur' => 'Bimbel Gambar Arsitektur',
        'kelas_gambar_anak' => 'Program Kelas Gambar Anak',
    ];

    public const TAHUN_AJARAN = [
        '2025 - 2026',
        '2026 - 2027',
        '2027 - 2028',
    ];

    public const SUBTITLES = [
        'kelas_gambar_anak' => 'Program kursus gambar SD, SMP, workshop, dan program liburan.',
    ];

    protected $table = 'produk_items';

    protected $fillable = [
        'program',
        'tahun_ajaran',
        'kode_1',
        'kode_2',
        'kode_3',
        'kode_4',
        'nama',
        'biaya_daftar',
        'biaya_pendidikan',
        'discount',
        'siswa',
        'omzet',
    ];

    protected function casts(): array
    {
        return [
            'biaya_daftar' => 'integer',
            'biaya_pendidikan' => 'integer',
            'discount' => 'integer',
            'siswa' => 'integer',
            'omzet' => 'integer',
        ];
    }

    public static function programOptions(): array
    {
        return self::PROGRAMS;
    }

    public static function tahunAjaranOptions(): array
    {
        return self::TAHUN_AJARAN;
    }

    public static function programSubtitle(string $program): ?string
    {
        return self::SUBTITLES[$program] ?? null;
    }

    public function labelProgram(): string
    {
        return self::PROGRAMS[$this->program] ?? $this->program;
    }
}
