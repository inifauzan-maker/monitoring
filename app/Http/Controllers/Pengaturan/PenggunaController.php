<?php

namespace App\Http\Controllers\Pengaturan;

use App\Enums\LevelAksesPengguna;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PencatatLogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(): View
    {
        return view('pengaturan.pengguna.index', [
            'penggunas' => User::query()
                ->orderByRaw($this->sqlUrutanLevelAkses())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('pengaturan.pengguna.formulir', [
            'pengguna' => new User([
                'level_akses' => LevelAksesPengguna::LEVEL_5,
            ]),
            'opsiLevelAkses' => LevelAksesPengguna::opsiSelect(),
            'judulForm' => 'Tambah Pengguna',
            'keteranganForm' => 'Buat akun baru sekaligus tentukan level aksesnya.',
            'aksiForm' => route('pengaturan.pengguna.store'),
            'metodeForm' => 'POST',
            'labelTombol' => 'Simpan Pengguna',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiPengguna($request);

        $penggunaBaru = User::create($data);
        PencatatLogAktivitas::catat(
            $request,
            'pengguna',
            'tambah',
            'Pengguna baru berhasil ditambahkan.',
            $penggunaBaru,
            [
                'email' => $penggunaBaru->email,
                'level_akses' => $penggunaBaru->level_akses?->value,
            ],
        );

        return redirect()
            ->route('pengaturan.pengguna.index')
            ->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $pengguna): View
    {
        return view('pengaturan.pengguna.formulir', [
            'pengguna' => $pengguna,
            'opsiLevelAkses' => LevelAksesPengguna::opsiSelect(),
            'judulForm' => 'Ubah Pengguna',
            'keteranganForm' => 'Perbarui identitas akun, email, password, dan level akses.',
            'aksiForm' => route('pengaturan.pengguna.update', $pengguna),
            'metodeForm' => 'PUT',
            'labelTombol' => 'Perbarui Pengguna',
        ]);
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $data = $this->validasiPengguna($request, $pengguna);
        $snapshotSebelum = [
            'name' => $pengguna->name,
            'email' => $pengguna->email,
            'level_akses' => $pengguna->level_akses?->value,
        ];

        if (
            $pengguna->adalahSuperadmin()
            && ($data['level_akses'] ?? null) !== LevelAksesPengguna::SUPERADMIN->value
            && ! $this->masihAdaSuperadminLainSelain($pengguna)
        ) {
            return back()
                ->withErrors(['level_akses' => 'Minimal harus ada satu akun superadmin.'])
                ->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $pengguna->update($data);
        $snapshotSesudah = [
            'name' => $pengguna->name,
            'email' => $pengguna->email,
            'level_akses' => $pengguna->level_akses?->value,
        ];
        $kolomDiubah = collect($snapshotSesudah)
            ->filter(fn ($value, string $key) => ($snapshotSebelum[$key] ?? null) !== $value)
            ->keys()
            ->values()
            ->all();

        PencatatLogAktivitas::catat(
            $request,
            'pengguna',
            'ubah',
            'Data pengguna diperbarui.',
            $pengguna,
            [
                'kolom_diubah' => $kolomDiubah,
                'sebelum' => $snapshotSebelum,
                'sesudah' => $snapshotSesudah,
            ],
        );

        return redirect()
            ->route('pengaturan.pengguna.index')
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($request->user()?->is($pengguna)) {
            return back()->withErrors([
                'pengguna' => 'Akun yang sedang Anda gunakan tidak bisa dihapus.',
            ]);
        }

        if ($pengguna->adalahSuperadmin() && ! $this->masihAdaSuperadminLainSelain($pengguna)) {
            return back()->withErrors([
                'pengguna' => 'Minimal harus ada satu akun superadmin.',
            ]);
        }

        PencatatLogAktivitas::catat(
            $request,
            'pengguna',
            'hapus',
            'Pengguna dihapus dari sistem.',
            $pengguna,
            [
                'email' => $pengguna->email,
                'level_akses' => $pengguna->level_akses?->value,
            ],
        );

        $pengguna->delete();

        return redirect()
            ->route('pengaturan.pengguna.index')
            ->with('status', 'Pengguna berhasil dihapus.');
    }

    private function validasiPengguna(Request $request, ?User $pengguna = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($pengguna),
            ],
            'level_akses' => ['required', Rule::in(LevelAksesPengguna::semuaNilai())],
            'password' => [
                $pengguna ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [], [
            'name' => 'nama',
            'email' => 'email',
            'level_akses' => 'level akses',
            'password' => 'password',
        ]);
    }

    private function masihAdaSuperadminLainSelain(User $pengguna): bool
    {
        return User::query()
            ->where('id', '!=', $pengguna->getKey())
            ->where('level_akses', LevelAksesPengguna::SUPERADMIN->value)
            ->exists();
    }

    private function sqlUrutanLevelAkses(): string
    {
        return <<<'SQL'
            CASE level_akses
                WHEN 'superadmin' THEN 0
                WHEN 'level_1' THEN 1
                WHEN 'level_2' THEN 2
                WHEN 'level_3' THEN 3
                WHEN 'level_4' THEN 4
                WHEN 'level_5' THEN 5
                ELSE 99
            END
        SQL;
    }
}
