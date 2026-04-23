<?php

namespace App\Support;

use App\Models\NotifikasiPengguna;
use App\Models\User;
use Illuminate\Support\Collection;

class PencatatNotifikasi
{
    /**
     * @param  \Illuminate\Support\Collection<int, User>|array<int, User>|User|null  $penerima
     */
    public static function kirim(
        Collection|array|User|null $penerima,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $tautan = null,
        array $metadata = [],
    ): void {
        self::normalisasiPenerima($penerima)
            ->each(function (User $item) use ($judul, $pesan, $tipe, $tautan, $metadata): void {
                NotifikasiPengguna::query()->create([
                    'user_id' => $item->id,
                    'judul' => $judul,
                    'pesan' => $pesan,
                    'tipe' => in_array($tipe, array_keys(NotifikasiPengguna::opsiTipe()), true) ? $tipe : 'info',
                    'tautan' => $tautan,
                    'metadata' => $metadata !== [] ? $metadata : null,
                ]);
            });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>|array<int, User>|User|null  $penerima
     * @return \Illuminate\Support\Collection<int, User>
     */
    private static function normalisasiPenerima(Collection|array|User|null $penerima): Collection
    {
        if ($penerima instanceof User) {
            return collect([$penerima]);
        }

        return collect($penerima)
            ->filter(fn ($item) => $item instanceof User && $item->exists)
            ->unique(fn (User $item) => (string) $item->id)
            ->values();
    }
}
