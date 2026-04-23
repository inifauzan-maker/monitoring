<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiPengguna;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $statusDipilih = $this->selectedStatus($request->query('status'));
        $tipeDipilih = $this->selectedTipe($request->query('tipe'));

        $query = $request->user()
            ->notifikasiPengguna()
            ->when($statusDipilih === 'belum_dibaca', fn ($builder) => $builder->whereNull('dibaca_pada'))
            ->when($statusDipilih === 'sudah_dibaca', fn ($builder) => $builder->whereNotNull('dibaca_pada'))
            ->when($tipeDipilih, fn ($builder) => $builder->where('tipe', $tipeDipilih))
            ->latest();

        $items = (clone $query)
            ->paginate(15)
            ->withQueryString();

        $ringkasanQuery = $request->user()->notifikasiPengguna();

        return view('notifikasi.index', [
            'items' => $items,
            'statusDipilih' => $statusDipilih,
            'tipeDipilih' => $tipeDipilih,
            'opsiStatus' => $this->opsiStatus(),
            'opsiTipe' => NotifikasiPengguna::opsiTipe(),
            'jumlahSemua' => (clone $ringkasanQuery)->count(),
            'jumlahBelumDibaca' => (clone $ringkasanQuery)->whereNull('dibaca_pada')->count(),
            'jumlahHariIni' => (clone $ringkasanQuery)->whereDate('created_at', now()->toDateString())->count(),
        ]);
    }

    public function baca(Request $request, NotifikasiPengguna $notifikasiPengguna): RedirectResponse
    {
        $this->otorisasiNotifikasi($request, $notifikasiPengguna);
        $notifikasiPengguna->tandaiSudahDibaca();

        return redirect($this->redirectTujuan($request))
            ->with('status', 'Notifikasi ditandai sudah dibaca.');
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $jumlahDiubah = $request->user()
            ->notifikasiPengguna()
            ->whereNull('dibaca_pada')
            ->update([
                'dibaca_pada' => now(),
                'updated_at' => now(),
            ]);

        $pesan = $jumlahDiubah > 0
            ? 'Semua notifikasi berhasil ditandai sudah dibaca.'
            : 'Tidak ada notifikasi baru untuk ditandai.';

        return redirect($this->redirectTujuan($request, route('notifikasi.index')))
            ->with('status', $pesan);
    }

    private function otorisasiNotifikasi(Request $request, NotifikasiPengguna $notifikasiPengguna): void
    {
        abort_unless(
            (int) $notifikasiPengguna->user_id === (int) $request->user()->id,
            403,
            'Akses ditolak.'
        );
    }

    private function redirectTujuan(Request $request, ?string $default = null): string
    {
        $tujuan = trim((string) $request->input('redirect_ke', ''));

        if ($tujuan !== '' && str_starts_with($tujuan, '/')) {
            return $tujuan;
        }

        $urlAplikasi = url('/');

        if ($tujuan !== '' && str_starts_with($tujuan, $urlAplikasi)) {
            return $tujuan;
        }

        return $default ?? route('notifikasi.index');
    }

    private function selectedStatus(mixed $nilai): ?string
    {
        return in_array($nilai, array_keys($this->opsiStatus()), true) ? (string) $nilai : null;
    }

    private function selectedTipe(mixed $nilai): ?string
    {
        return in_array($nilai, array_keys(NotifikasiPengguna::opsiTipe()), true) ? (string) $nilai : null;
    }

    private function opsiStatus(): array
    {
        return [
            'belum_dibaca' => 'Belum Dibaca',
            'sudah_dibaca' => 'Sudah Dibaca',
        ];
    }
}
