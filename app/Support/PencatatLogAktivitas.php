<?php

namespace App\Support;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PencatatLogAktivitas
{
    public static function catat(
        Request $request,
        string $modul,
        string $aksi,
        string $deskripsi,
        Model|string|null $subjek = null,
        array $metadata = [],
        ?User $aktor = null,
    ): LogAktivitas {
        [$subjekTipe, $subjekId] = self::resolveSubjek($subjek);

        return LogAktivitas::query()->create([
            'user_id' => $aktor?->id ?? $request->user()?->id,
            'modul' => $modul,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'subjek_tipe' => $subjekTipe,
            'subjek_id' => $subjekId,
            'metadata' => $metadata !== [] ? $metadata : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private static function resolveSubjek(Model|string|null $subjek): array
    {
        if ($subjek instanceof Model) {
            $nama = method_exists($subjek, 'getTable')
                ? $subjek->getTable()
                : class_basename($subjek);

            return [$nama, $subjek->getKey()];
        }

        if (is_string($subjek) && filled($subjek)) {
            return [$subjek, null];
        }

        return [null, null];
    }
}
