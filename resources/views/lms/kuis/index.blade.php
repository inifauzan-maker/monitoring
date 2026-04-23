@extends('tataletak.aplikasi')

@section('judul_halaman', 'Kuis')
@section('pratitel_halaman', 'LMS')
@section('judul_konten', 'Kuis')
@section('deskripsi_halaman', 'Kelola dan kerjakan kuis yang bisa ditempelkan ke kursus maupun materi LMS.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.kursus') }}" class="btn btn-outline-secondary">Kursus</a>
        <a href="{{ route('lms.materi') }}" class="btn btn-outline-secondary">Materi</a>
        <a href="{{ route('lms.bank_soal') }}" class="btn btn-outline-secondary">Bank Soal</a>
        @if ($isSuperadmin)
            <a href="#form-kuis" class="btn btn-primary">{{ $editItem ? 'Ubah Kuis' : 'Tambah Kuis' }}</a>
        @endif
    </div>
@endsection

@section('konten')
    @php
        $jumlahKuis = $items->count();
        $jumlahAktif = $items->where('aktif', true)->count();
        $jumlahBankSoal = $items->where('gunakan_bank_soal', true)->count();
        $jumlahAcak = $items->filter(fn ($item) => $item->acak_soal || $item->acak_opsi_jawaban)->count();
        $parameterFilter = [];

        if ($targetTipe) {
            $parameterFilter['target_tipe'] = $targetTipe;
        }

        if ($kursusDipilih) {
            $parameterFilter['kursus'] = $kursusDipilih->id;
        }

        if ($materiDipilih) {
            $parameterFilter['materi'] = $materiDipilih->id;
        }

        if ($kataKunci) {
            $parameterFilter['q'] = $kataKunci;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Kuis</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahKuis, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Kuis Aktif</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahAktif, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Mode Bank Soal</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahBankSoal, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Mode Acak Aktif</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahAcak, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Kuis</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-3">
                            <label class="form-label" for="target_tipe">Target</label>
                            <select class="form-select" id="target_tipe" name="target_tipe">
                                <option value="">Semua Target</option>
                                @foreach ($opsiTarget as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($targetTipe === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="kursus">Kursus</label>
                            <select class="form-select" id="kursus" name="kursus">
                                <option value="">Semua Kursus</option>
                                @foreach ($opsiKursus as $kursus)
                                    <option value="{{ $kursus->id }}" @selected($kursusDipilih?->id === $kursus->id)>{{ $kursus->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="materi">Materi</label>
                            <select class="form-select" id="materi" name="materi">
                                <option value="">Semua Materi</option>
                                @foreach ($opsiMateri as $materi)
                                    <option value="{{ $materi->id }}" @selected($materiDipilih?->id === $materi->id)>{{ $materi->kursus?->judul }} - {{ $materi->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="q">Kata Kunci</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $kataKunci }}" placeholder="Cari judul atau target">
                        </div>
                        <div class="col-12">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($parameterFilter !== [])
                                    <a href="{{ route('lms.kuis') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-kuis">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">{{ $editItem ? 'Ubah Kuis' : 'Tambah Kuis' }}</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('lms.kuis.update', $editItem) : route('lms.kuis.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            <input type="hidden" name="filter_target_tipe" value="{{ $targetTipe }}">
                            <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                            <input type="hidden" name="filter_materi" value="{{ $materiDipilih?->id }}">
                            <input type="hidden" name="filter_q" value="{{ $kataKunci }}">

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label" for="judul">Judul Kuis</label>
                                    <input type="text" class="form-control" id="judul" name="judul" value="{{ old('judul', $editItem?->judul) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="nilai_lulus">Nilai Lulus</label>
                                    <input type="number" class="form-control" id="nilai_lulus" name="nilai_lulus" min="0" max="100" value="{{ old('nilai_lulus', $editItem?->nilai_lulus ?? 70) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="jumlah_soal_tampil">Jumlah Soal Tampil</label>
                                    <input type="number" class="form-control" id="jumlah_soal_tampil" name="jumlah_soal_tampil" min="1" max="500" value="{{ old('jumlah_soal_tampil', $editItem?->jumlah_soal_tampil) }}" placeholder="Kosongkan untuk semua">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="durasi_menit">Durasi Timer (menit)</label>
                                    <input type="number" class="form-control" id="durasi_menit" name="durasi_menit" min="1" max="720" value="{{ old('durasi_menit', $editItem?->durasi_menit) }}" placeholder="Kosongkan jika tanpa timer">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="maksimal_percobaan">Maksimal Percobaan</label>
                                    <input type="number" class="form-control" id="maksimal_percobaan" name="maksimal_percobaan" min="1" max="100" value="{{ old('maksimal_percobaan', $editItem?->maksimal_percobaan) }}" placeholder="Kosongkan jika fleksibel">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="target_tipe_form">Target</label>
                                    <select class="form-select" id="target_tipe_form" name="target_tipe" required>
                                        @foreach ($opsiTarget as $nilai => $label)
                                            <option value="{{ $nilai }}" @selected(old('target_tipe', $editItem?->target_tipe ?? $targetTipe) === $nilai)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="kursus_id">Kursus</label>
                                    <select class="form-select" id="kursus_id" name="kursus_id">
                                        <option value="">Pilih Kursus</option>
                                        @foreach ($opsiKursus as $kursus)
                                            <option value="{{ $kursus->id }}" @selected((int) old('kursus_id', $editItem?->kursus_id ?? $kursusDipilih?->id) === $kursus->id)>{{ $kursus->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="materi_kursus_id">Materi</label>
                                    <select class="form-select" id="materi_kursus_id" name="materi_kursus_id">
                                        <option value="">Pilih Materi</option>
                                        @foreach ($opsiMateri as $materi)
                                            <option value="{{ $materi->id }}" @selected((int) old('materi_kursus_id', $editItem?->materi_kursus_id ?? $materiDipilih?->id) === $materi->id)>{{ $materi->kursus?->judul }} - {{ $materi->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-check">
                                                <input type="hidden" name="gunakan_bank_soal" value="0">
                                                <input class="form-check-input" type="checkbox" name="gunakan_bank_soal" value="1" @checked(old('gunakan_bank_soal', $editItem?->gunakan_bank_soal ?? false))>
                                                <span class="form-check-label">Gunakan bank soal reusable</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check">
                                                <input type="hidden" name="acak_soal" value="0">
                                                <input class="form-check-input" type="checkbox" name="acak_soal" value="1" @checked(old('acak_soal', $editItem?->acak_soal ?? false))>
                                                <span class="form-check-label">Aktifkan randomisasi soal</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-check">
                                                <input type="hidden" name="acak_opsi_jawaban" value="0">
                                                <input class="form-check-input" type="checkbox" name="acak_opsi_jawaban" value="1" @checked(old('acak_opsi_jawaban', $editItem?->acak_opsi_jawaban ?? false))>
                                                <span class="form-check-label">Acak urutan opsi jawaban</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $editItem?->deskripsi) }}</textarea>
                                    <div class="form-hint">Jika `Jumlah Soal Tampil` diisi dan randomisasi aktif, sistem akan menampilkan subset soal acak dari sumber yang tersedia. Isi `Durasi Timer` jika pengerjaan ingin dibatasi waktu, dan isi `Maksimal Percobaan` jika ingin membatasi jumlah submit per pengguna.</div>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <div class="fw-semibold mb-1">Mode sumber soal</div>
                                        <div class="text-secondary">Jika `Gunakan bank soal reusable` aktif, pertanyaan manual di detail kuis tidak dipakai. Soal akan diambil dari menu `Bank Soal` dan ditempelkan ke kuis.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0">
                                        <div class="fw-semibold mb-1">Randomisasi jawaban</div>
                                        <div class="text-secondary">Jika `Acak urutan opsi jawaban` aktif, sistem menyimpan mapping opsi dalam paket sesi, jadi penilaian tetap akurat walau urutan jawaban berubah.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-check">
                                        <input type="hidden" name="aktif" value="0">
                                        <input class="form-check-input" type="checkbox" name="aktif" value="1" @checked(old('aktif', $editItem?->aktif ?? true))>
                                        <span class="form-check-label">Kuis aktif dan bisa dikerjakan pengguna</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">{{ $editItem ? 'Simpan Perubahan' : 'Simpan Kuis' }}</button>
                                @if ($editItem)
                                    <a href="{{ route('lms.kuis', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Kuis</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Target</th>
                                <th>Pertanyaan</th>
                                <th>Nilai Lulus</th>
                                <th>Status</th>
                                <th>Hasil Saya</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php($hasilSaya = $item->hasilPengguna->first())
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->judul }}</div>
                                        <div class="text-secondary small">{{ $item->deskripsi ? \Illuminate\Support\Str::limit($item->deskripsi, 80) : 'Tanpa deskripsi.' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt text-blue">{{ $item->labelTarget() }}</span>
                                        <div class="small text-secondary mt-1">{{ $item->namaTarget() }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-lt text-secondary">{{ $item->labelSumberSoal() }}</span>
                                        <div class="small text-secondary mt-1">
                                            {{ number_format($item->jumlahSoalSumber(), 0, ',', '.') }} sumber
                                            · tampil {{ number_format($item->jumlahSoalTampilEfektif(), 0, ',', '.') }}
                                        </div>
                                        @if ($item->acak_soal)
                                            <div class="small text-secondary">Soal diacak saat pengerjaan</div>
                                        @endif
                                        @if ($item->acak_opsi_jawaban)
                                            <div class="small text-secondary">Opsi jawaban diacak</div>
                                        @endif
                                        <div class="small text-secondary">{{ $item->labelDurasiTimer() }}</div>
                                        <div class="small text-secondary">{{ $item->labelMaksimalPercobaan() }}</div>
                                    </td>
                                    <td>{{ $item->nilai_lulus }}%</td>
                                    <td><span class="badge {{ $item->kelasBadgeAktif() }}">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td>
                                        @if ($hasilSaya)
                                            <span class="badge {{ $hasilSaya->kelasBadgeHasil() }}">{{ $hasilSaya->labelHasil() }}</span>
                                            <div class="small text-secondary mt-1">Skor {{ $hasilSaya->skor }}% · {{ $hasilSaya->jumlah_percobaan }}x percobaan</div>
                                        @else
                                            <span class="text-secondary small">Belum dikerjakan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-list justify-content-end flex-nowrap">
                                            <a href="{{ route('lms.kuis.show', $item) }}" class="btn btn-primary btn-sm">Buka</a>
                                            @if ($isSuperadmin)
                                                <a href="{{ route('lms.kuis.edit', ['kuisLms' => $item] + $parameterFilter) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <form method="POST" action="{{ route('lms.kuis.destroy', $item) }}" onsubmit="return confirm('Hapus kuis ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="filter_target_tipe" value="{{ $targetTipe }}">
                                                    <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                                                    <input type="hidden" name="filter_materi" value="{{ $materiDipilih?->id }}">
                                                    <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty py-5">
                                            <p class="empty-title">Belum ada kuis</p>
                                            <p class="empty-subtitle text-secondary mb-0">Tambahkan kuis pertama atau ubah filter untuk melihat data lain.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
