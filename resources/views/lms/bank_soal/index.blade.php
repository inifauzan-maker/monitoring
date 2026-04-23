@extends('tataletak.aplikasi')

@section('judul_halaman', 'Bank Soal')
@section('pratitel_halaman', 'LMS')
@section('judul_konten', 'Bank Soal')
@section('deskripsi_halaman', 'Kelola kumpulan soal reusable yang bisa ditempelkan ke kuis LMS dan dipakai untuk randomisasi soal.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.kuis') }}" class="btn btn-outline-secondary">Kuis</a>
        @if ($isSuperadmin)
            <a href="#form-bank-soal" class="btn btn-primary">{{ $editItem ? 'Ubah Soal' : 'Tambah Soal' }}</a>
        @endif
    </div>
@endsection

@section('konten')
    @php
        $jumlahSoal = $items->count();
        $jumlahAktif = $items->where('aktif', true)->count();
        $jumlahTerpakai = $items->where('kuis_lms_count', '>', 0)->count();
        $jumlahSulit = $items->where('tingkat_kesulitan', 'sulit')->count();
        $parameterFilter = [];

        if ($kursusDipilih) {
            $parameterFilter['kursus'] = $kursusDipilih->id;
        }

        if (! is_null($statusAktif)) {
            $parameterFilter['aktif'] = $statusAktif ? '1' : '0';
        }

        if ($tingkatKesulitan) {
            $parameterFilter['tingkat_kesulitan'] = $tingkatKesulitan;
        }

        if ($kataKunci) {
            $parameterFilter['q'] = $kataKunci;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Total Soal</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahSoal, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Soal Aktif</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahAktif, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Dipakai Kuis</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahTerpakai, 0, ',', '.') }}</div></div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body"><div class="text-secondary text-uppercase fw-semibold small">Level Sulit</div><div class="h1 mb-0 mt-2">{{ number_format($jumlahSulit, 0, ',', '.') }}</div></div></div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Bank Soal</h3></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-3">
                            <label class="form-label" for="kursus">Kursus</label>
                            <select class="form-select" id="kursus" name="kursus">
                                <option value="">Semua Kursus</option>
                                @foreach ($opsiKursus as $kursus)
                                    <option value="{{ $kursus->id }}" @selected($kursusDipilih?->id === $kursus->id)>{{ $kursus->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label" for="aktif">Status</label>
                            <select class="form-select" id="aktif" name="aktif">
                                <option value="">Semua Status</option>
                                <option value="1" @selected($statusAktif === true)>Aktif</option>
                                <option value="0" @selected($statusAktif === false)>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label" for="tingkat_kesulitan">Kesulitan</label>
                            <select class="form-select" id="tingkat_kesulitan" name="tingkat_kesulitan">
                                <option value="">Semua Level</option>
                                @foreach ($opsiTingkatKesulitan as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($tingkatKesulitan === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label" for="q">Kata Kunci</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ $kataKunci }}" placeholder="Cari pertanyaan atau kursus">
                        </div>
                        <div class="col-12">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($parameterFilter !== [])
                                    <a href="{{ route('lms.bank_soal') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            <div class="col-12" id="form-bank-soal">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">{{ $editItem ? 'Ubah Soal' : 'Tambah Soal' }}</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('lms.bank_soal.update', $editItem) : route('lms.bank_soal.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                            <input type="hidden" name="filter_aktif" value="{{ ! is_null($statusAktif) ? ($statusAktif ? '1' : '0') : '' }}">
                            <input type="hidden" name="filter_tingkat_kesulitan" value="{{ $tingkatKesulitan }}">
                            <input type="hidden" name="filter_q" value="{{ $kataKunci }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="kursus_id">Kursus</label>
                                    <select class="form-select" id="kursus_id" name="kursus_id">
                                        <option value="">Global / Semua Kursus</option>
                                        @foreach ($opsiKursus as $kursus)
                                            <option value="{{ $kursus->id }}" @selected((int) old('kursus_id', $editItem?->kursus_id ?? $kursusDipilih?->id) === $kursus->id)>{{ $kursus->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="tingkat_kesulitan_form">Tingkat Kesulitan</label>
                                    <select class="form-select" id="tingkat_kesulitan_form" name="tingkat_kesulitan" required>
                                        @foreach ($opsiTingkatKesulitan as $nilai => $label)
                                            <option value="{{ $nilai }}" @selected(old('tingkat_kesulitan', $editItem?->tingkat_kesulitan ?? 'menengah') === $nilai)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-check mt-4">
                                        <input type="hidden" name="aktif" value="0">
                                        <input class="form-check-input" type="checkbox" name="aktif" value="1" @checked(old('aktif', $editItem?->aktif ?? true))>
                                        <span class="form-check-label">Soal aktif dan bisa dipakai kuis</span>
                                    </label>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="pertanyaan">Pertanyaan</label>
                                    <textarea class="form-control" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan', $editItem?->pertanyaan) }}</textarea>
                                </div>
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="col-md-6">
                                        <label class="form-label" for="opsi_{{ $i + 1 }}">Opsi {{ $i + 1 }}</label>
                                        <input type="text" class="form-control" id="opsi_{{ $i + 1 }}" name="opsi_{{ $i + 1 }}"
                                            value="{{ old('opsi_'.($i + 1), $editItem?->opsi_jawaban[$i] ?? '') }}" required>
                                    </div>
                                @endfor
                                <div class="col-md-4">
                                    <label class="form-label" for="indeks_jawaban_benar">Jawaban Benar</label>
                                    <select class="form-select" id="indeks_jawaban_benar" name="indeks_jawaban_benar" required>
                                        @for ($i = 0; $i < 4; $i++)
                                            <option value="{{ $i }}" @selected((int) old('indeks_jawaban_benar', $editItem?->indeks_jawaban_benar ?? 0) === $i)>Opsi {{ $i + 1 }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">{{ $editItem ? 'Simpan Perubahan' : 'Simpan Soal' }}</button>
                                @if ($editItem)
                                    <a href="{{ route('lms.bank_soal', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Bank Soal</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Pertanyaan</th>
                                <th>Kursus</th>
                                <th>Kesulitan</th>
                                <th>Status</th>
                                <th>Dipakai</th>
                                <th>Jawaban Benar</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($item->pertanyaan, 120) }}</div>
                                        <div class="text-secondary small">{{ collect($item->opsi_jawaban)->implode(' · ') }}</div>
                                    </td>
                                    <td>{{ $item->kursus?->judul ?? 'Global' }}</td>
                                    <td><span class="badge {{ $item->kelasBadgeKesulitan() }}">{{ $item->labelTingkatKesulitan() }}</span></td>
                                    <td><span class="badge {{ $item->kelasBadgeAktif() }}">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td>{{ number_format($item->kuis_lms_count, 0, ',', '.') }} kuis</td>
                                    <td>{{ $item->jawabanBenar() }}</td>
                                    <td>
                                        @if ($isSuperadmin)
                                            <div class="btn-list justify-content-end flex-nowrap">
                                                <a href="{{ route('lms.bank_soal.edit', ['bankSoalLms' => $item] + $parameterFilter) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
                                                <form method="POST" action="{{ route('lms.bank_soal.destroy', $item) }}" onsubmit="return confirm('Hapus soal bank ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="filter_kursus" value="{{ $kursusDipilih?->id }}">
                                                    <input type="hidden" name="filter_aktif" value="{{ ! is_null($statusAktif) ? ($statusAktif ? '1' : '0') : '' }}">
                                                    <input type="hidden" name="filter_tingkat_kesulitan" value="{{ $tingkatKesulitan }}">
                                                    <input type="hidden" name="filter_q" value="{{ $kataKunci }}">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-secondary small">Lihat saja</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty py-5">
                                            <p class="empty-title">Belum ada soal di bank soal</p>
                                            <p class="empty-subtitle text-secondary mb-0">Tambahkan soal reusable untuk dipakai kuis LMS.</p>
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
