<?php

namespace App\Http\Controllers;

use App\Models\DataSiswa;
use App\Models\ProdukItem;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderIndex($request);
    }

    public function edit(Request $request, DataSiswa $siswa): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderIndex($request, $siswa);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->preparePayload($this->validatedData($request), null, $request);

        $siswa = DataSiswa::create($data);
        PencatatLogAktivitas::catat(
            $request,
            'siswa',
            'tambah',
            'Data siswa baru ditambahkan.',
            $siswa,
            [
                'nama_lengkap' => $siswa->nama_lengkap,
                'program' => $siswa->program,
                'status_validasi' => $siswa->status_validasi,
                'status_pembayaran' => $siswa->status_pembayaran,
            ],
        );

        return redirect()
            ->route('siswa.index', $this->filterQuery(
                $request->input('filter_program'),
                $request->input('filter_status_validasi'),
                $request->input('filter_status_pembayaran'),
                $request->input('filter_lokasi'),
                $request->input('filter_kode'),
            ))
            ->with('status', 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, DataSiswa $siswa): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $snapshotSebelum = [
            'nama_lengkap' => $siswa->nama_lengkap,
            'program' => $siswa->program,
            'status_validasi' => $siswa->status_validasi,
            'status_pembayaran' => $siswa->status_pembayaran,
            'jumlah_pembayaran' => $siswa->jumlah_pembayaran,
        ];
        $data = $this->preparePayload($this->validatedData($request), $siswa, $request);

        $siswa->update($data);
        $snapshotSesudah = [
            'nama_lengkap' => $siswa->nama_lengkap,
            'program' => $siswa->program,
            'status_validasi' => $siswa->status_validasi,
            'status_pembayaran' => $siswa->status_pembayaran,
            'jumlah_pembayaran' => $siswa->jumlah_pembayaran,
        ];
        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();
        PencatatLogAktivitas::catat(
            $request,
            'siswa',
            'ubah',
            'Data siswa diperbarui.',
            $siswa,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );

        return redirect()
            ->route('siswa.index', $this->filterQuery(
                $request->input('filter_program'),
                $request->input('filter_status_validasi'),
                $request->input('filter_status_pembayaran'),
                $request->input('filter_lokasi'),
                $request->input('filter_kode'),
            ))
            ->with('status', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Request $request, DataSiswa $siswa): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        PencatatLogAktivitas::catat(
            $request,
            'siswa',
            'hapus',
            'Data siswa dihapus.',
            $siswa,
            [
                'nama_lengkap' => $siswa->nama_lengkap,
                'program' => $siswa->program,
                'status_validasi' => $siswa->status_validasi,
            ],
        );

        $siswa->delete();

        return redirect()
            ->route('siswa.index', $this->filterQuery(
                $request->input('filter_program'),
                $request->input('filter_status_validasi'),
                $request->input('filter_status_pembayaran'),
                $request->input('filter_lokasi'),
                $request->input('filter_kode'),
            ))
            ->with('status', 'Data siswa berhasil dihapus.');
    }

    private function renderIndex(Request $request, ?DataSiswa $editItem = null): View
    {
        $selectedProgram = $this->selectedProgram($request->query('program'));
        $selectedStatusValidasi = $this->selectedStatusValidasi($request->query('status_validasi'));
        $selectedStatusPembayaran = $this->selectedStatusPembayaran($request->query('status_pembayaran'));
        $selectedLokasi = $this->selectedText($request->query('lokasi'));
        $selectedKode = $this->selectedText($request->query('kode'));

        $baseQuery = $this->querySiswa(
            $selectedProgram,
            $selectedStatusValidasi,
            $selectedStatusPembayaran,
            $selectedLokasi,
            $selectedKode,
        );

        $items = (clone $baseQuery)
            ->with(['produk', 'validator'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = (clone $baseQuery)->get();

        return view('siswa.index', [
            'items' => $items,
            'ringkasan' => $ringkasan,
            'programs' => ProdukItem::programOptions(),
            'statusValidasiOptions' => DataSiswa::statusValidasiOptions(),
            'statusPembayaranOptions' => DataSiswa::statusPembayaranOptions(),
            'sistemPembayaranOptions' => DataSiswa::sistemPembayaranOptions(),
            'selectedProgram' => $selectedProgram,
            'selectedStatusValidasi' => $selectedStatusValidasi,
            'selectedStatusPembayaran' => $selectedStatusPembayaran,
            'selectedLokasi' => $selectedLokasi,
            'selectedKode' => $selectedKode,
            'lokasiOptions' => $this->lokasiOptions(),
            'kodeOptions' => $this->kodeOptions(),
            'produkOptions' => ProdukItem::query()->orderBy('program')->orderBy('nama')->get(),
            'editItem' => $editItem,
            'isSuperadmin' => $request->user()?->adalahSuperadmin() ?? false,
        ]);
    }

    private function querySiswa(
        ?string $program,
        ?string $statusValidasi,
        ?string $statusPembayaran,
        ?string $lokasi,
        ?string $kode,
    ): Builder {
        return DataSiswa::query()
            ->when($program, fn (Builder $query) => $query->where('program', $program))
            ->when($statusValidasi, fn (Builder $query) => $query->where('status_validasi', $statusValidasi))
            ->when($statusPembayaran, fn (Builder $query) => $query->where('status_pembayaran', $statusPembayaran))
            ->when($lokasi, fn (Builder $query) => $query->where('lokasi_belajar', $lokasi))
            ->when($kode, function (Builder $query) use ($kode) {
                $query->whereHas('produk', function (Builder $builder) use ($kode) {
                    $builder->where('kode_1', 'like', '%'.$kode.'%')
                        ->orWhere('kode_2', 'like', '%'.$kode.'%')
                        ->orWhere('kode_3', 'like', '%'.$kode.'%')
                        ->orWhere('kode_4', 'like', '%'.$kode.'%');
                });
            });
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'produk_item_id' => ['required', 'exists:produk_items,id'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'tingkat_kelas' => ['nullable', 'string', 'max:50'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'nomor_telepon' => ['nullable', 'string', 'max:32'],
            'nama_orang_tua' => ['nullable', 'string', 'max:150'],
            'nomor_telepon_orang_tua' => ['nullable', 'string', 'max:32'],
            'lokasi_belajar' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
            'sistem_pembayaran' => ['required', Rule::in(array_keys(DataSiswa::sistemPembayaranOptions()))],
            'status_validasi' => ['required', Rule::in(array_keys(DataSiswa::statusValidasiOptions()))],
            'total_invoice' => ['nullable', 'integer', 'min:0'],
            'jumlah_pembayaran' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ], [], [
            'produk_item_id' => 'produk',
            'nama_lengkap' => 'nama lengkap',
            'asal_sekolah' => 'asal sekolah',
            'tingkat_kelas' => 'tingkat kelas',
            'nomor_telepon' => 'nomor telepon',
            'nama_orang_tua' => 'nama orang tua',
            'nomor_telepon_orang_tua' => 'nomor telepon orang tua',
            'lokasi_belajar' => 'lokasi belajar',
            'status_validasi' => 'status validasi',
            'total_invoice' => 'total tagihan',
            'jumlah_pembayaran' => 'jumlah pembayaran',
        ]);
    }

    private function preparePayload(array $data, ?DataSiswa $existing, Request $request): array
    {
        $produk = ProdukItem::query()->findOrFail($data['produk_item_id']);
        $totalInvoice = isset($data['total_invoice'])
            ? (int) $data['total_invoice']
            : max(0, (int) $produk->biaya_daftar + (int) $produk->biaya_pendidikan - (int) $produk->discount);

        $jumlahPembayaran = (int) ($data['jumlah_pembayaran'] ?? 0);
        $sisaTagihan = max($totalInvoice - $jumlahPembayaran, 0);

        $data['program'] = $produk->program;
        $data['nama_program'] = $produk->nama;
        $data['total_invoice'] = $totalInvoice;
        $data['jumlah_pembayaran'] = $jumlahPembayaran;
        $data['sisa_tagihan'] = $sisaTagihan;
        $data['status_pembayaran'] = $this->resolveStatusPembayaran($jumlahPembayaran, $sisaTagihan);
        $data['tanggal_pembayaran'] = $jumlahPembayaran > 0 ? ($existing?->tanggal_pembayaran ?? now()) : null;

        if ($data['status_validasi'] === 'validated') {
            $data['tanggal_validasi'] = $existing?->tanggal_validasi ?? now();
            $data['divalidasi_oleh'] = $existing?->divalidasi_oleh ?? $request->user()->id;
            $data['nomor_invoice'] = $existing?->nomor_invoice ?? $this->makeInvoiceNumber();
        } else {
            $data['tanggal_validasi'] = null;
            $data['divalidasi_oleh'] = null;
            $data['nomor_invoice'] = null;
        }

        return $data;
    }

    private function resolveStatusPembayaran(int $jumlahPembayaran, int $sisaTagihan): string
    {
        if ($jumlahPembayaran <= 0) {
            return 'belum_bayar';
        }

        return $sisaTagihan > 0 ? 'sebagian' : 'lunas';
    }

    private function makeInvoiceNumber(): string
    {
        return 'INV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function selectedProgram(?string $value): ?string
    {
        return in_array($value, array_keys(ProdukItem::programOptions()), true) ? $value : null;
    }

    private function selectedStatusValidasi(?string $value): ?string
    {
        return in_array($value, array_keys(DataSiswa::statusValidasiOptions()), true) ? $value : null;
    }

    private function selectedStatusPembayaran(?string $value): ?string
    {
        return in_array($value, array_keys(DataSiswa::statusPembayaranOptions()), true) ? $value : null;
    }

    private function selectedText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function lokasiOptions()
    {
        return DataSiswa::query()
            ->select('lokasi_belajar')
            ->whereNotNull('lokasi_belajar')
            ->where('lokasi_belajar', '!=', '')
            ->distinct()
            ->orderBy('lokasi_belajar')
            ->pluck('lokasi_belajar');
    }

    private function kodeOptions()
    {
        $lists = [
            ProdukItem::query()->select('kode_1')->whereNotNull('kode_1')->where('kode_1', '!=', '')->distinct()->pluck('kode_1'),
            ProdukItem::query()->select('kode_2')->whereNotNull('kode_2')->where('kode_2', '!=', '')->distinct()->pluck('kode_2'),
            ProdukItem::query()->select('kode_3')->whereNotNull('kode_3')->where('kode_3', '!=', '')->distinct()->pluck('kode_3'),
            ProdukItem::query()->select('kode_4')->whereNotNull('kode_4')->where('kode_4', '!=', '')->distinct()->pluck('kode_4'),
        ];

        return collect($lists)
            ->flatten()
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function filterQuery(
        ?string $program,
        ?string $statusValidasi,
        ?string $statusPembayaran,
        ?string $lokasi,
        ?string $kode,
    ): array {
        $query = [];

        if ($selectedProgram = $this->selectedProgram($program)) {
            $query['program'] = $selectedProgram;
        }

        if ($selectedStatusValidasi = $this->selectedStatusValidasi($statusValidasi)) {
            $query['status_validasi'] = $selectedStatusValidasi;
        }

        if ($selectedStatusPembayaran = $this->selectedStatusPembayaran($statusPembayaran)) {
            $query['status_pembayaran'] = $selectedStatusPembayaran;
        }

        if ($selectedLokasi = $this->selectedText($lokasi)) {
            $query['lokasi'] = $selectedLokasi;
        }

        if ($selectedKode = $this->selectedText($kode)) {
            $query['kode'] = $selectedKode;
        }

        return $query;
    }
}
