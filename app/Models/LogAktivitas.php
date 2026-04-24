<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LogAktivitas extends Model
{
    public const OPSI_MODUL = [
        'autentikasi' => 'Autentikasi',
        'pengguna' => 'Pengguna',
        'proyek' => 'Project',
        'tugas_proyek' => 'Tugas Project',
        'omzet' => 'Omzet',
        'artikel' => 'Artikel',
        'kursus' => 'Kursus',
        'materi' => 'Materi',
        'playlist' => 'Playlist',
        'progres_belajar' => 'Progres Belajar',
        'kuis' => 'Kuis',
        'bank_soal' => 'Bank Soal',
        'pemetaan_beranda' => 'Pemetaan Beranda',
        'produk' => 'Produk',
        'siswa' => 'Siswa',
        'lead' => 'Leads',
        'link' => 'Link',
    ];

    public const OPSI_AKSI = [
        'masuk' => 'Masuk',
        'masuk_gagal' => 'Masuk Gagal',
        'keluar' => 'Keluar',
        'tambah' => 'Tambah',
        'ubah' => 'Ubah',
        'hapus' => 'Hapus',
        'status_cepat' => 'Status Cepat',
        'tindak_lanjut' => 'Tindak Lanjut',
        'impor' => 'Impor',
        'lihat' => 'Lihat',
        'ekspor' => 'Ekspor',
        'submit' => 'Submit',
    ];

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'modul',
        'aksi',
        'deskripsi',
        'subjek_tipe',
        'subjek_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public static function opsiModul(): array
    {
        return self::OPSI_MODUL;
    }

    public static function opsiAksi(): array
    {
        return self::OPSI_AKSI;
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function labelModul(): string
    {
        return self::OPSI_MODUL[$this->modul] ?? Str::headline((string) $this->modul);
    }

    public function labelAksi(): string
    {
        return self::OPSI_AKSI[$this->aksi] ?? Str::headline((string) $this->aksi);
    }

    public function kelasBadgeAksi(): string
    {
        return match ($this->aksi) {
            'masuk' => 'bg-green-lt text-green',
            'keluar' => 'bg-blue-lt text-blue',
            'masuk_gagal' => 'bg-red-lt text-red',
            'hapus' => 'bg-red-lt text-red',
            'lihat' => 'bg-blue-lt text-blue',
            'ekspor' => 'bg-indigo-lt text-indigo',
            'impor' => 'bg-green-lt text-green',
            'submit' => 'bg-green-lt text-green',
            'ubah', 'status_cepat', 'tindak_lanjut' => 'bg-yellow-lt text-yellow',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function labelSubjek(): string
    {
        if (! filled($this->subjek_tipe)) {
            return '-';
        }

        return Str::headline(str_replace('_', ' ', (string) $this->subjek_tipe))
            .($this->subjek_id ? ' #'.$this->subjek_id : '');
    }
}
