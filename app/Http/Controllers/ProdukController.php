<?php

namespace App\Http\Controllers;

use App\Models\ProdukItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $selectedTahun = $this->selectedTahun(
            $request->query('tahun_ajaran'),
            $request->has('tahun_ajaran'),
        );
        $selectedProgram = $this->selectedProgram($request->query('program'));
        $selectedKode = $this->selectedKode($request->query('kode'));
        $items = $this->queryItems($selectedTahun, $selectedProgram, $selectedKode)->get();

        return view('produk.index', [
            'items' => $items,
            'grouped' => $items->groupBy('program'),
            'programs' => ProdukItem::programOptions(),
            'tahunOptions' => ProdukItem::tahunAjaranOptions(),
            'selectedTahun' => $selectedTahun,
            'selectedProgram' => $selectedProgram,
            'selectedKode' => $selectedKode,
            'kodeOptions' => $this->kodeOptions(),
            'editItem' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->validatedData($request);
        $data = $this->normalizeProgram($data);
        $data['omzet'] = $this->calculateOmzet($data);

        ProdukItem::create($data);

        return redirect()
            ->route('produk.index', $this->filterQuery(
                $request->input('filter_tahun'),
                $request->input('filter_program'),
                $request->input('filter_kode'),
            ))
            ->with('status', 'Data produk berhasil ditambahkan.');
    }

    public function edit(Request $request, ProdukItem $produk): View
    {
        $this->authorizeSuperadmin($request);

        $selectedTahun = $this->selectedTahun(
            $request->query('tahun_ajaran'),
            $request->has('tahun_ajaran'),
        );
        $selectedProgram = $this->selectedProgram($request->query('program'));
        $selectedKode = $this->selectedKode($request->query('kode'));
        $items = $this->queryItems($selectedTahun, $selectedProgram, $selectedKode)->get();

        return view('produk.index', [
            'items' => $items,
            'grouped' => $items->groupBy('program'),
            'programs' => ProdukItem::programOptions(),
            'tahunOptions' => ProdukItem::tahunAjaranOptions(),
            'selectedTahun' => $selectedTahun,
            'selectedProgram' => $selectedProgram,
            'selectedKode' => $selectedKode,
            'kodeOptions' => $this->kodeOptions(),
            'editItem' => $produk,
        ]);
    }

    public function update(Request $request, ProdukItem $produk): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->validatedData($request);
        $data = $this->normalizeProgram($data);
        $data['omzet'] = $this->calculateOmzet($data);

        $produk->update($data);

        return redirect()
            ->route('produk.index', $this->filterQuery(
                $request->input('filter_tahun'),
                $request->input('filter_program'),
                $request->input('filter_kode'),
            ))
            ->with('status', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Request $request, ProdukItem $produk): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $produk->delete();

        return redirect()
            ->route('produk.index', $this->filterQuery(
                $request->input('filter_tahun'),
                $request->input('filter_program'),
                $request->input('filter_kode'),
            ))
            ->with('status', 'Data produk berhasil dihapus.');
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function validatedData(Request $request): array
    {
        $programKeys = array_keys(ProdukItem::programOptions());
        $programKeys[] = 'custom';

        return $request->validate([
            'program' => ['required', Rule::in($programKeys)],
            'program_custom' => ['required_if:program,custom', 'nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['required', Rule::in(ProdukItem::tahunAjaranOptions())],
            'kode_1' => ['required', 'string', 'max:10'],
            'kode_2' => ['required', 'string', 'max:10'],
            'kode_3' => ['required', 'string', 'max:10'],
            'kode_4' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', 'max:120'],
            'biaya_daftar' => ['required', 'integer', 'min:0'],
            'biaya_pendidikan' => ['required', 'integer', 'min:0'],
            'discount' => ['required', 'integer', 'min:0'],
            'siswa' => ['required', 'integer', 'min:0'],
        ], [], [
            'program' => 'program',
            'program_custom' => 'program manual',
            'tahun_ajaran' => 'tahun ajaran',
            'kode_1' => 'kode 1',
            'kode_2' => 'kode 2',
            'kode_3' => 'kode 3',
            'kode_4' => 'kode 4',
            'nama' => 'program kelas',
            'biaya_daftar' => 'biaya daftar',
            'biaya_pendidikan' => 'biaya pendidikan',
            'discount' => 'diskon',
            'siswa' => 'target siswa',
        ]);
    }

    private function normalizeProgram(array $data): array
    {
        if ($data['program'] === 'custom') {
            $data['program'] = $data['program_custom'];
        }

        unset($data['program_custom']);

        return $data;
    }

    private function calculateOmzet(array $data): int
    {
        $subtotal = (int) $data['biaya_daftar']
            + (int) $data['biaya_pendidikan']
            - (int) $data['discount'];

        if ($subtotal < 0) {
            $subtotal = 0;
        }

        return $subtotal * (int) $data['siswa'];
    }

    private function selectedTahun(?string $value, bool $hasFilter): ?string
    {
        if ($hasFilter) {
            return $this->normalizeTahun($value);
        }

        $default = '2026 - 2027';

        return in_array($default, ProdukItem::tahunAjaranOptions(), true) ? $default : null;
    }

    private function normalizeTahun(?string $value): ?string
    {
        return in_array($value, ProdukItem::tahunAjaranOptions(), true) ? $value : null;
    }

    private function selectedProgram(?string $value): ?string
    {
        return in_array($value, array_keys(ProdukItem::programOptions()), true) ? $value : null;
    }

    private function selectedKode(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function filterQuery(?string $tahun, ?string $program, ?string $kode = null): array
    {
        $query = [];

        if ($selectedTahun = $this->normalizeTahun($tahun)) {
            $query['tahun_ajaran'] = $selectedTahun;
        }

        if ($selectedProgram = $this->selectedProgram($program)) {
            $query['program'] = $selectedProgram;
        }

        if ($selectedKode = $this->selectedKode($kode)) {
            $query['kode'] = $selectedKode;
        }

        return $query;
    }

    private function queryItems(?string $tahun, ?string $program, ?string $kode)
    {
        $query = ProdukItem::query()->orderBy('program')->orderBy('id');

        if ($tahun) {
            $query->where('tahun_ajaran', $tahun);
        }

        if ($program) {
            $query->where('program', $program);
        }

        if ($kode) {
            $query->where(function ($builder) use ($kode) {
                $builder->where('kode_1', 'like', '%'.$kode.'%')
                    ->orWhere('kode_2', 'like', '%'.$kode.'%')
                    ->orWhere('kode_3', 'like', '%'.$kode.'%')
                    ->orWhere('kode_4', 'like', '%'.$kode.'%');
            });
        }

        return $query;
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
}
