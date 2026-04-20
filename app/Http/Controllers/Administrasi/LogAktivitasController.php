<?php

namespace App\Http\Controllers\Administrasi;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $selectedModul = $this->selectedModul($request->query('modul'));
        $selectedAksi = $this->selectedAksi($request->query('aksi'));
        $selectedPengguna = $this->selectedPengguna($request->query('user_id'));
        $tanggalDari = $this->parseTanggal($request->query('tanggal_dari'));
        $tanggalSampai = $this->parseTanggal($request->query('tanggal_sampai'));
        $kataKunci = trim((string) $request->query('q'));

        $baseQuery = LogAktivitas::query()
            ->with('pengguna')
            ->when($selectedModul, fn (Builder $query) => $query->where('modul', $selectedModul))
            ->when($selectedAksi, fn (Builder $query) => $query->where('aksi', $selectedAksi))
            ->when($selectedPengguna, fn (Builder $query) => $query->where('user_id', $selectedPengguna))
            ->when($tanggalDari, fn (Builder $query) => $query->whereDate('created_at', '>=', $tanggalDari))
            ->when($tanggalSampai, fn (Builder $query) => $query->whereDate('created_at', '<=', $tanggalSampai))
            ->when($kataKunci !== '', function (Builder $query) use ($kataKunci) {
                $query->where(function (Builder $inner) use ($kataKunci) {
                    $inner
                        ->where('deskripsi', 'like', '%'.$kataKunci.'%')
                        ->orWhere('ip_address', 'like', '%'.$kataKunci.'%')
                        ->orWhere('subjek_tipe', 'like', '%'.$kataKunci.'%')
                        ->orWhereHas('pengguna', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', '%'.$kataKunci.'%')
                            ->orWhere('email', 'like', '%'.$kataKunci.'%'));
                });
            });

        $items = (clone $baseQuery)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $ringkasanQuery = (clone $baseQuery);
        $ringkasan = (clone $ringkasanQuery)->get();
        $hariIni = Carbon::today();

        return view('administrasi.log_aktivitas.index', [
            'items' => $items,
            'opsiModul' => LogAktivitas::opsiModul(),
            'opsiAksi' => LogAktivitas::opsiAksi(),
            'opsiPengguna' => User::query()->orderBy('name')->get(),
            'selectedModul' => $selectedModul,
            'selectedAksi' => $selectedAksi,
            'selectedPengguna' => $selectedPengguna,
            'tanggalDari' => $tanggalDari,
            'tanggalSampai' => $tanggalSampai,
            'kataKunci' => $kataKunci,
            'ringkasan' => [
                'total' => $ringkasan->count(),
                'hari_ini' => $ringkasan->filter(fn (LogAktivitas $item) => $item->created_at?->isSameDay($hariIni))->count(),
                'autentikasi' => $ringkasan->where('modul', 'autentikasi')->count(),
                'perubahan_data' => $ringkasan->whereIn('aksi', ['tambah', 'ubah', 'hapus', 'status_cepat'])->count(),
            ],
        ]);
    }

    private function selectedModul(?string $value): ?string
    {
        return array_key_exists((string) $value, LogAktivitas::opsiModul()) ? $value : null;
    }

    private function selectedAksi(?string $value): ?string
    {
        return array_key_exists((string) $value, LogAktivitas::opsiAksi()) ? $value : null;
    }

    private function selectedPengguna(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 && User::query()->whereKey($value)->exists() ? $value : null;
    }

    private function parseTanggal(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
