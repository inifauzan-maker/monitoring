<?php

namespace App\Enums;

enum ProfilBerandaPengguna: string
{
    case STRATEGIS = 'strategis';
    case PENGAWASAN = 'pengawasan';
    case KOORDINASI = 'koordinasi';
    case OPERASIONAL = 'operasional';
    case KONTEN = 'konten';
    case PERSONAL = 'personal';
    case EKSEKUSI = 'eksekusi';

    public function label(): string
    {
        return match ($this) {
            self::STRATEGIS => 'Strategis',
            self::PENGAWASAN => 'Pengawasan',
            self::KOORDINASI => 'Koordinasi',
            self::OPERASIONAL => 'Operasional',
            self::KONTEN => 'Konten & Distribusi',
            self::PERSONAL => 'Personal',
            self::EKSEKUSI => 'Eksekusi Harian',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::STRATEGIS => 'Cocok untuk ringkasan lintas modul, target besar, dan keputusan manajerial.',
            self::PENGAWASAN => 'Cocok untuk pengawasan funnel, validasi, pelaporan, dan monitoring tim.',
            self::KOORDINASI => 'Cocok untuk sinkronisasi antar tugas, follow up, dan pergerakan project.',
            self::OPERASIONAL => 'Cocok untuk ritme kerja lapangan, tindak lanjut leads, dan eksekusi modul harian.',
            self::KONTEN => 'Cocok untuk artikel, distribusi link, kanal kampanye, dan ritme produksi konten.',
            self::PERSONAL => 'Cocok untuk ruang kerja umum yang berfokus pada tugas, belajar, dan notifikasi pribadi.',
            self::EKSEKUSI => 'Cocok untuk tampilan paling ringkas agar fokus hanya ke prioritas terdekat.',
        };
    }

    public function pratinjau(): array
    {
        return match ($this) {
            self::STRATEGIS => [
                'badge' => 'Mode strategis',
                'judul' => 'Arahan lintas modul',
                'indikator' => 'Target Omzet',
                'jalur_cepat' => 'Jalur Cepat Manajerial',
                'fokus' => ['Omzet & siswa', 'Editorial', 'Project prioritas'],
            ],
            self::PENGAWASAN => [
                'badge' => 'Mode pengawasan',
                'judul' => 'Funnel & pengawasan',
                'indikator' => 'Titik Pantau',
                'jalur_cepat' => 'Jalur Cepat Pengawasan',
                'fokus' => ['Leads aktif', 'Validasi siswa', 'Pelaporan tim'],
            ],
            self::KOORDINASI => [
                'badge' => 'Mode koordinasi',
                'judul' => 'Sinkronisasi tim',
                'indikator' => 'Tugas Terbuka',
                'jalur_cepat' => 'Jalur Cepat Koordinasi',
                'fokus' => ['Penanggung jawab', 'Project berjalan', 'Tindak lanjut'],
            ],
            self::OPERASIONAL => [
                'badge' => 'Mode operasional',
                'judul' => 'Lapangan & distribusi',
                'indikator' => 'Progress Hari Ini',
                'jalur_cepat' => 'Jalur Cepat Operasional',
                'fokus' => ['Leads', 'Siswa', 'Kampanye aktif'],
            ],
            self::KONTEN => [
                'badge' => 'Mode konten',
                'judul' => 'Konten & distribusi',
                'indikator' => 'Kesiapan Konten',
                'jalur_cepat' => 'Jalur Cepat Konten',
                'fokus' => ['Artikel', 'Link publik', 'Kanal distribusi'],
            ],
            self::PERSONAL => [
                'badge' => 'Mode personal',
                'judul' => 'Ruang kerja pribadi',
                'indikator' => 'Kinerja Pribadi',
                'jalur_cepat' => 'Jalur Cepat Personal',
                'fokus' => ['Tugas saya', 'Materi belajar', 'Notifikasi baru'],
            ],
            self::EKSEKUSI => [
                'badge' => 'Mode eksekusi',
                'judul' => 'Eksekusi harian',
                'indikator' => 'Prioritas Terdekat',
                'jalur_cepat' => 'Jalur Cepat Eksekusi',
                'fokus' => ['Checklist hari ini', 'Tugas kritis', 'Kanal aktif'],
            ],
        };
    }

    public static function defaultUntukLevel(LevelAksesPengguna $levelAkses): self
    {
        return match ($levelAkses) {
            LevelAksesPengguna::LEVEL_1 => self::PENGAWASAN,
            LevelAksesPengguna::LEVEL_2 => self::KOORDINASI,
            LevelAksesPengguna::LEVEL_3 => self::OPERASIONAL,
            LevelAksesPengguna::LEVEL_4 => self::KONTEN,
            LevelAksesPengguna::LEVEL_5 => self::EKSEKUSI,
            LevelAksesPengguna::SUPERADMIN => self::STRATEGIS,
        };
    }

    public static function opsiSelect(): array
    {
        $opsi = [];

        foreach (self::cases() as $profil) {
            $opsi[$profil->value] = $profil->label();
        }

        return $opsi;
    }

    public static function semuaNilai(): array
    {
        return array_map(
            static fn (self $profil): string => $profil->value,
            self::cases(),
        );
    }
}
