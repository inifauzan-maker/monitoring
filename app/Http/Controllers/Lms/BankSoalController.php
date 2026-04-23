<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\BankSoalLms;
use App\Models\Kursus;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankSoalController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderHalaman($request);
    }

    public function edit(Request $request, BankSoalLms $bankSoalLms): View
    {
        $this->authorizeSuperadmin($request);

        return $this->renderHalaman($request, $bankSoalLms);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $this->validatedData($request);
        $bankSoal = BankSoalLms::query()->create($data);
        $bankSoal->load('kursus');

        PencatatLogAktivitas::catat(
            $request,
            'bank_soal',
            'tambah',
            'Bank soal LMS baru ditambahkan.',
            $bankSoal,
            $this->metadataBankSoal($bankSoal),
        );

        return redirect()
            ->route('lms.bank_soal', $this->filterQuery($request))
            ->with('status', 'Soal bank berhasil ditambahkan.');
    }

    public function update(Request $request, BankSoalLms $bankSoalLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $snapshotSebelum = $this->metadataBankSoal($bankSoalLms->load('kursus'));
        $data = $this->validatedData($request);
        $bankSoalLms->update($data);
        $bankSoalLms->load('kursus');

        PencatatLogAktivitas::catat(
            $request,
            'bank_soal',
            'ubah',
            'Bank soal LMS diperbarui.',
            $bankSoalLms,
            [
                'sebelum' => $snapshotSebelum,
                'sesudah' => $this->metadataBankSoal($bankSoalLms),
            ],
        );

        return redirect()
            ->route('lms.bank_soal', $this->filterQuery($request))
            ->with('status', 'Soal bank berhasil diperbarui.');
    }

    public function destroy(Request $request, BankSoalLms $bankSoalLms): RedirectResponse
    {
        $this->authorizeSuperadmin($request);
        $bankSoalLms->load('kursus');

        PencatatLogAktivitas::catat(
            $request,
            'bank_soal',
            'hapus',
            'Bank soal LMS dihapus.',
            $bankSoalLms,
            $this->metadataBankSoal($bankSoalLms),
        );

        $bankSoalLms->delete();

        return redirect()
            ->route('lms.bank_soal', $this->filterQuery($request))
            ->with('status', 'Soal bank berhasil dihapus.');
    }

    private function renderHalaman(Request $request, ?BankSoalLms $editItem = null): View
    {
        $kursusDipilih = $this->selectedKursus($request->query('kursus'));
        $statusAktif = $this->selectedStatusAktif($request->query('aktif'));
        $tingkatKesulitan = $this->selectedTingkatKesulitan($request->query('tingkat_kesulitan'));
        $kataKunci = $this->selectedKataKunci($request->query('q'));
        $items = $this->queryItems($kursusDipilih?->id, $statusAktif, $tingkatKesulitan, $kataKunci)->get();

        return view('lms.bank_soal.index', [
            'items' => $items,
            'opsiKursus' => Kursus::query()->orderBy('judul')->get(['id', 'judul']),
            'opsiTingkatKesulitan' => BankSoalLms::opsiTingkatKesulitan(),
            'kursusDipilih' => $kursusDipilih,
            'statusAktif' => $statusAktif,
            'tingkatKesulitan' => $tingkatKesulitan,
            'kataKunci' => $kataKunci,
            'editItem' => $editItem?->load('kursus'),
            'isSuperadmin' => $request->user()->adalahSuperadmin(),
        ]);
    }

    private function queryItems(?int $kursusId, ?bool $statusAktif, ?string $tingkatKesulitan, ?string $kataKunci)
    {
        return BankSoalLms::query()
            ->with('kursus')
            ->withCount('kuisLms')
            ->when($kursusId, fn ($query) => $query->where('kursus_id', $kursusId))
            ->when(! is_null($statusAktif), fn ($query) => $query->where('aktif', $statusAktif))
            ->when($tingkatKesulitan, fn ($query) => $query->where('tingkat_kesulitan', $tingkatKesulitan))
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($builder) use ($kataKunci) {
                    $builder->where('pertanyaan', 'like', '%'.$kataKunci.'%')
                        ->orWhereHas('kursus', fn ($relasi) => $relasi->where('judul', 'like', '%'.$kataKunci.'%'));
                });
            })
            ->latest();
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'kursus_id' => ['nullable', 'integer', 'exists:kursus,id'],
            'pertanyaan' => ['required', 'string', 'max:2000'],
            'opsi_1' => ['required', 'string', 'max:255'],
            'opsi_2' => ['required', 'string', 'max:255'],
            'opsi_3' => ['required', 'string', 'max:255'],
            'opsi_4' => ['required', 'string', 'max:255'],
            'indeks_jawaban_benar' => ['required', 'integer', 'between:0,3'],
            'tingkat_kesulitan' => ['required', 'in:mudah,menengah,sulit'],
            'aktif' => ['nullable', 'boolean'],
        ], [], [
            'kursus_id' => 'kursus',
            'pertanyaan' => 'pertanyaan',
            'opsi_1' => 'opsi jawaban 1',
            'opsi_2' => 'opsi jawaban 2',
            'opsi_3' => 'opsi jawaban 3',
            'opsi_4' => 'opsi jawaban 4',
            'indeks_jawaban_benar' => 'jawaban benar',
            'tingkat_kesulitan' => 'tingkat kesulitan',
            'aktif' => 'status aktif',
        ]);

        return [
            'kursus_id' => filled($validated['kursus_id'] ?? null) ? (int) $validated['kursus_id'] : null,
            'pertanyaan' => trim((string) $validated['pertanyaan']),
            'opsi_jawaban' => [
                trim((string) $validated['opsi_1']),
                trim((string) $validated['opsi_2']),
                trim((string) $validated['opsi_3']),
                trim((string) $validated['opsi_4']),
            ],
            'indeks_jawaban_benar' => (int) $validated['indeks_jawaban_benar'],
            'tingkat_kesulitan' => $validated['tingkat_kesulitan'],
            'aktif' => $request->boolean('aktif'),
        ];
    }

    private function selectedKursus(mixed $nilai): ?Kursus
    {
        $nilai = (int) $nilai;

        return $nilai > 0 ? Kursus::query()->find($nilai) : null;
    }

    private function selectedStatusAktif(mixed $nilai): ?bool
    {
        if ($nilai === '1' || $nilai === 1 || $nilai === true) {
            return true;
        }

        if ($nilai === '0' || $nilai === 0 || $nilai === false) {
            return false;
        }

        return null;
    }

    private function selectedTingkatKesulitan(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return array_key_exists($nilai, BankSoalLms::opsiTingkatKesulitan()) ? $nilai : null;
    }

    private function selectedKataKunci(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return $nilai !== '' ? $nilai : null;
    }

    private function metadataBankSoal(BankSoalLms $bankSoalLms): array
    {
        return [
            'kursus_id' => $bankSoalLms->kursus_id,
            'kursus' => $bankSoalLms->kursus?->judul,
            'pertanyaan' => $bankSoalLms->pertanyaan,
            'opsi_jawaban' => $bankSoalLms->opsi_jawaban,
            'indeks_jawaban_benar' => $bankSoalLms->indeks_jawaban_benar,
            'tingkat_kesulitan' => $bankSoalLms->tingkat_kesulitan,
            'aktif' => $bankSoalLms->aktif,
        ];
    }

    private function filterQuery(Request $request): array
    {
        $query = [];

        if ($kursus = $this->selectedKursus($request->input('filter_kursus'))) {
            $query['kursus'] = $kursus->id;
        }

        $statusAktif = $request->input('filter_aktif');

        if ($statusAktif === '1' || $statusAktif === '0') {
            $query['aktif'] = $statusAktif;
        }

        if ($tingkatKesulitan = $this->selectedTingkatKesulitan($request->input('filter_tingkat_kesulitan'))) {
            $query['tingkat_kesulitan'] = $tingkatKesulitan;
        }

        if ($kataKunci = $this->selectedKataKunci($request->input('filter_q'))) {
            $query['q'] = $kataKunci;
        }

        return $query;
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }
}
