<?php

namespace App\Models;

use App\Enums\LevelAksesPengguna;
use App\Enums\ProfilBerandaPengguna;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PemetaanBerandaLevel extends Model
{
    protected $table = 'pemetaan_beranda_level';

    protected $fillable = [
        'level_akses',
        'profil_beranda',
    ];

    protected function casts(): array
    {
        return [
            'level_akses' => LevelAksesPengguna::class,
            'profil_beranda' => ProfilBerandaPengguna::class,
        ];
    }

    public static function daftarLevelYangDapatDiatur(): array
    {
        return array_values(array_filter(
            LevelAksesPengguna::cases(),
            static fn (LevelAksesPengguna $levelAkses): bool => $levelAkses !== LevelAksesPengguna::SUPERADMIN,
        ));
    }

    public static function sinkronkanDefault(): void
    {
        foreach (self::daftarLevelYangDapatDiatur() as $levelAkses) {
            self::query()->firstOrCreate(
                ['level_akses' => $levelAkses->value],
                ['profil_beranda' => ProfilBerandaPengguna::defaultUntukLevel($levelAkses)->value],
            );
        }
    }

    public static function profilUntuk(LevelAksesPengguna $levelAkses): ProfilBerandaPengguna
    {
        if ($levelAkses === LevelAksesPengguna::SUPERADMIN) {
            return ProfilBerandaPengguna::defaultUntukLevel($levelAkses);
        }

        $pemetaan = self::query()
            ->where('level_akses', $levelAkses->value)
            ->first();

        if ($pemetaan?->profil_beranda instanceof ProfilBerandaPengguna) {
            return $pemetaan->profil_beranda;
        }

        $rawProfil = $pemetaan?->getRawOriginal('profil_beranda');

        if (is_string($rawProfil) && in_array($rawProfil, ProfilBerandaPengguna::semuaNilai(), true)) {
            return ProfilBerandaPengguna::from($rawProfil);
        }

        return ProfilBerandaPengguna::defaultUntukLevel($levelAkses);
    }

    public static function daftarTerlengkapi(): Collection
    {
        self::sinkronkanDefault();

        $pemetaan = self::query()
            ->get()
            ->keyBy(fn (self $item) => $item->level_akses?->value);

        return collect(self::daftarLevelYangDapatDiatur())
            ->map(function (LevelAksesPengguna $levelAkses) use ($pemetaan): array {
                /** @var self|null $item */
                $item = $pemetaan->get($levelAkses->value);
                $profilAktif = $item?->profil_beranda ?? ProfilBerandaPengguna::defaultUntukLevel($levelAkses);

                return [
                    'level_akses' => $levelAkses,
                    'profil_aktif' => $profilAktif,
                    'profil_bawaan' => ProfilBerandaPengguna::defaultUntukLevel($levelAkses),
                ];
            });
    }
}
