<?php

namespace App\Http\Controllers\Administrasi;

use App\Enums\ProfilBerandaPengguna;
use App\Http\Controllers\Controller;
use App\Models\PemetaanBerandaLevel;
use App\Support\PencatatLogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PemetaanBerandaController extends Controller
{
    public function index(Request $request): View
    {
        PencatatLogAktivitas::catat(
            $request,
            'pemetaan_beranda',
            'lihat',
            'Membuka halaman pemetaan beranda per level.',
            'pemetaan_beranda_level',
        );

        return view('administrasi.pemetaan_beranda.index', [
            'items' => PemetaanBerandaLevel::daftarTerlengkapi(),
            'opsiProfilBeranda' => collect(ProfilBerandaPengguna::cases())
                ->mapWithKeys(fn (ProfilBerandaPengguna $profil) => [
                    $profil->value => [
                        'label' => $profil->label(),
                        'keterangan' => $profil->keterangan(),
                        'pratinjau' => $profil->pratinjau(),
                    ],
                ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'profil' => ['required', 'array'],
            'profil.*' => ['required', Rule::in(ProfilBerandaPengguna::semuaNilai())],
        ], [], [
            'profil' => 'profil beranda',
        ]);

        $payload = [];
        $snapshot = [];

        foreach (PemetaanBerandaLevel::daftarLevelYangDapatDiatur() as $levelAkses) {
            $profilBeranda = ProfilBerandaPengguna::from(
                $data['profil'][$levelAkses->value] ?? ProfilBerandaPengguna::defaultUntukLevel($levelAkses)->value
            );

            $payload[] = [
                'level_akses' => $levelAkses->value,
                'profil_beranda' => $profilBeranda->value,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $snapshot[$levelAkses->value] = $profilBeranda->value;
        }

        PemetaanBerandaLevel::query()->upsert(
            $payload,
            ['level_akses'],
            ['profil_beranda', 'updated_at'],
        );

        PencatatLogAktivitas::catat(
            $request,
            'pemetaan_beranda',
            'ubah',
            'Pemetaan profil beranda per level diperbarui.',
            'pemetaan_beranda_level',
            [
                'profil' => $snapshot,
            ],
        );

        return redirect()
            ->route('administrasi.pemetaan_beranda.index')
            ->with('status', 'Pemetaan profil beranda berhasil diperbarui.');
    }
}
