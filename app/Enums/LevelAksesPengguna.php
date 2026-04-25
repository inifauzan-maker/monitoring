<?php

namespace App\Enums;

enum LevelAksesPengguna: string
{
    case SUPERADMIN = 'superadmin';
    case LEVEL_1 = 'level_1';
    case LEVEL_2 = 'level_2';
    case LEVEL_3 = 'level_3';
    case LEVEL_4 = 'level_4';
    case LEVEL_5 = 'level_5';

    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Superadmin',
            self::LEVEL_1 => 'Admin',
            self::LEVEL_2 => 'Owner/Direksi',
            self::LEVEL_3 => 'PIC',
            self::LEVEL_4 => 'Leader',
            self::LEVEL_5 => 'Staff',
        };
    }

    public function urutan(): int
    {
        return match ($this) {
            self::SUPERADMIN => 0,
            self::LEVEL_1 => 1,
            self::LEVEL_2 => 2,
            self::LEVEL_3 => 3,
            self::LEVEL_4 => 4,
            self::LEVEL_5 => 5,
        };
    }

    public function kelasBadge(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'bg-red-lt text-red',
            self::LEVEL_1 => 'bg-blue-lt text-blue',
            self::LEVEL_2 => 'bg-indigo-lt text-indigo',
            self::LEVEL_3 => 'bg-azure-lt text-azure',
            self::LEVEL_4 => 'bg-lime-lt text-lime',
            self::LEVEL_5 => 'bg-secondary-lt text-secondary',
        };
    }

    public static function opsiSelect(): array
    {
        $opsi = [];

        foreach (self::cases() as $levelAkses) {
            $opsi[$levelAkses->value] = $levelAkses->label();
        }

        return $opsi;
    }

    public static function semuaNilai(): array
    {
        return array_map(
            static fn (self $levelAkses): string => $levelAkses->value,
            self::cases(),
        );
    }
}
