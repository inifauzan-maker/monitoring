<?php

namespace App\Http\Controllers;

use App\Models\DataSiswa;
use App\Models\ProdukItem;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OmzetController extends Controller
{
    public function index(Request $request): View
    {
        $yearOptions = DataSiswa::query()
            ->whereNotNull('created_at')
            ->get(['created_at'])
            ->pluck('created_at')
            ->map(fn ($tanggal) => $tanggal?->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if ($yearOptions->isEmpty()) {
            $yearOptions = collect([now()->year]);
        }

        $selectedTahun = (int) $request->query('tahun', $yearOptions->first());
        if (! $yearOptions->contains($selectedTahun)) {
            $selectedTahun = (int) $yearOptions->first();
        }

        $selectedBulan = (int) $request->query('bulan', 0);
        if (! in_array($selectedBulan, range(0, 12), true)) {
            $selectedBulan = 0;
        }

        $selectedProgram = in_array($request->query('program'), array_keys(ProdukItem::programOptions()), true)
            ? $request->query('program')
            : null;

        $selectedStatusPembayaran = in_array($request->query('status_pembayaran'), array_keys(DataSiswa::statusPembayaranOptions()), true)
            ? $request->query('status_pembayaran')
            : null;

        $baseQuery = DataSiswa::query()
            ->with('produk')
            ->where('status_validasi', 'validated')
            ->when($selectedTahun > 0, fn (Builder $query) => $query->whereYear('created_at', $selectedTahun))
            ->when($selectedBulan > 0, fn (Builder $query) => $query->whereMonth('created_at', $selectedBulan))
            ->when($selectedProgram, fn (Builder $query) => $query->where('program', $selectedProgram))
            ->when($selectedStatusPembayaran, fn (Builder $query) => $query->where('status_pembayaran', $selectedStatusPembayaran));

        $rekap = (clone $baseQuery)->get();

        $perProgram = $rekap
            ->groupBy('program')
            ->map(function ($items, $program) {
                return [
                    'program' => $program,
                    'label' => ProdukItem::programOptions()[$program] ?? $program,
                    'jumlah_siswa' => $items->count(),
                    'total_invoice' => (int) $items->sum('total_invoice'),
                    'jumlah_pembayaran' => (int) $items->sum('jumlah_pembayaran'),
                    'sisa_tagihan' => (int) $items->sum('sisa_tagihan'),
                ];
            })
            ->sortByDesc('total_invoice')
            ->values();

        $transaksi = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
        $totalInvoice = (int) $rekap->sum('total_invoice');
        $totalPembayaran = (int) $rekap->sum('jumlah_pembayaran');
        $totalSisaTagihan = (int) $rekap->sum('sisa_tagihan');
        $jumlahSiswaLunas = (int) $rekap->where('status_pembayaran', 'lunas')->count();

        PencatatLogAktivitas::catat(
            $request,
            'omzet',
            'lihat',
            'Membuka rekap omzet.',
            'laporan_omzet',
            [
                'filter' => [
                    'tahun' => $selectedTahun,
                    'bulan' => $selectedBulan > 0 ? $selectedBulan : null,
                    'program' => $selectedProgram,
                    'status_pembayaran' => $selectedStatusPembayaran,
                    'halaman' => $transaksi->currentPage(),
                ],
                'ringkasan' => [
                    'jumlah_transaksi' => $transaksi->total(),
                    'jumlah_program' => $perProgram->count(),
                    'total_invoice' => $totalInvoice,
                    'total_pembayaran' => $totalPembayaran,
                    'total_sisa_tagihan' => $totalSisaTagihan,
                    'jumlah_siswa_lunas' => $jumlahSiswaLunas,
                ],
            ]
        );

        return view('omzet.index', [
            'transaksi' => $transaksi,
            'perProgram' => $perProgram,
            'programs' => ProdukItem::programOptions(),
            'statusPembayaranOptions' => DataSiswa::statusPembayaranOptions(),
            'yearOptions' => $yearOptions,
            'selectedTahun' => $selectedTahun,
            'selectedBulan' => $selectedBulan,
            'selectedProgram' => $selectedProgram,
            'selectedStatusPembayaran' => $selectedStatusPembayaran,
            'totalInvoice' => $totalInvoice,
            'totalPembayaran' => $totalPembayaran,
            'totalSisaTagihan' => $totalSisaTagihan,
            'jumlahSiswaLunas' => $jumlahSiswaLunas,
        ]);
    }
}
