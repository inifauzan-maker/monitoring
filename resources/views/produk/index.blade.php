@extends('tataletak.aplikasi')

@section('judul_halaman', 'Produk')
@section('pratitel_halaman', 'Modul')
@section('judul_konten', 'Produk')
@section('deskripsi_halaman', 'Kelola data produk bimbel dengan filter program, tahun ajaran, kode, dan hitung omzet otomatis.')

@section('aksi_halaman')
    <div class="btn-list">
        @if (auth()->user()->adalahSuperadmin())
            <a href="#form-produk" class="btn btn-primary">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5l0 14" />
                    <path d="M5 12l14 0" />
                </svg>
                {{ $editItem ? 'Ubah Produk' : 'Tambah Produk' }}
            </a>
        @endif
    </div>
@endsection

@section('konten')
    @php
        $isSuperadmin = auth()->user()->adalahSuperadmin();
        $totalOmzet = $items->sum('omzet');
        $totalSiswa = $items->sum('siswa');
        $jumlahData = $items->count();

        $parameterFilter = [];

        if ($selectedTahun) {
            $parameterFilter['tahun_ajaran'] = $selectedTahun;
        }

        if ($selectedProgram) {
            $parameterFilter['program'] = $selectedProgram;
        }

        if ($selectedKode) {
            $parameterFilter['kode'] = $selectedKode;
        }
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Target Omzet</div>
                    <div class="h2 mb-0 mt-2">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Produk</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($jumlahData, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Target Siswa</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalSiswa, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Tahun Aktif</div>
                    <div class="h3 mb-0 mt-2">{{ $selectedTahun ?: 'Semua Tahun' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Produk</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4 col-xl-3">
                            <label class="form-label" for="filter_program">Program</label>
                            <select class="form-select" id="filter_program" name="program">
                                <option value="">Semua Program</option>
                                @foreach ($programs as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedProgram === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl-3">
                            <label class="form-label" for="filter_tahun_ajaran">Tahun Ajaran</label>
                            <select class="form-select" id="filter_tahun_ajaran" name="tahun_ajaran">
                                <option value="">Semua Tahun</option>
                                @foreach ($tahunOptions as $option)
                                    <option value="{{ $option }}" @selected($selectedTahun === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl-3">
                            <label class="form-label" for="filter_kode">Kode</label>
                            <select class="form-select" id="filter_kode" name="kode">
                                <option value="">Semua Kode</option>
                                @foreach ($kodeOptions as $kode)
                                    <option value="{{ $kode }}" @selected($selectedKode === $kode)>{{ $kode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($selectedTahun || $selectedProgram || $selectedKode)
                                    <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($isSuperadmin)
            @php
                $selectedProgramValue = old('program', $editItem?->program ?? $selectedProgram);
                $isCustomProgram = $selectedProgramValue && !array_key_exists($selectedProgramValue, $programs);
                $resolvedProgramValue = $isCustomProgram ? 'custom' : $selectedProgramValue;
                $customProgramValue = $isCustomProgram ? $selectedProgramValue : old('program_custom');
            @endphp

            <div class="col-12" id="form-produk">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $editItem ? 'Ubah Produk' : 'Tambah Produk' }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editItem ? route('produk.update', $editItem) : route('produk.store') }}">
                            @csrf
                            @if ($editItem)
                                @method('PUT')
                            @endif

                            @if ($selectedTahun)
                                <input type="hidden" name="filter_tahun" value="{{ $selectedTahun }}">
                            @endif
                            @if ($selectedProgram)
                                <input type="hidden" name="filter_program" value="{{ $selectedProgram }}">
                            @endif
                            @if ($selectedKode)
                                <input type="hidden" name="filter_kode" value="{{ $selectedKode }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label" for="program">Program</label>
                                    <select class="form-select" name="program" id="program" required>
                                        <option value="">Pilih Program</option>
                                        @foreach ($programs as $key => $label)
                                            <option value="{{ $key }}" @selected($resolvedProgramValue === $key)>{{ $label }}</option>
                                        @endforeach
                                        <option value="custom" @selected($resolvedProgramValue === 'custom')>Lainnya (isi manual)</option>
                                    </select>
                                </div>

                                <div class="col-md-6 col-xl-4 {{ $resolvedProgramValue === 'custom' ? '' : 'd-none' }}" id="program_custom_wrap">
                                    <label class="form-label" for="program_custom">Program Manual</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="program_custom"
                                        name="program_custom"
                                        value="{{ $customProgramValue }}"
                                        placeholder="Isi jika program belum ada"
                                    >
                                </div>

                                <div class="col-md-6 col-xl-4">
                                    <label class="form-label" for="tahun_ajaran">Tahun Ajaran</label>
                                    <select class="form-select" name="tahun_ajaran" id="tahun_ajaran" required>
                                        @foreach ($tahunOptions as $option)
                                            <option value="{{ $option }}" @selected(old('tahun_ajaran', $editItem?->tahun_ajaran ?? $selectedTahun) === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 col-xl-3">
                                    <label class="form-label" for="kode_1">Kode 1</label>
                                    <input type="text" class="form-control" id="kode_1" name="kode_1" value="{{ old('kode_1', $editItem?->kode_1) }}" required>
                                </div>
                                <div class="col-md-3 col-xl-3">
                                    <label class="form-label" for="kode_2">Kode 2</label>
                                    <input type="text" class="form-control" id="kode_2" name="kode_2" value="{{ old('kode_2', $editItem?->kode_2) }}" required>
                                </div>
                                <div class="col-md-3 col-xl-3">
                                    <label class="form-label" for="kode_3">Kode 3</label>
                                    <input type="text" class="form-control" id="kode_3" name="kode_3" value="{{ old('kode_3', $editItem?->kode_3) }}" required>
                                </div>
                                <div class="col-md-3 col-xl-3">
                                    <label class="form-label" for="kode_4">Kode 4</label>
                                    <input type="text" class="form-control" id="kode_4" name="kode_4" value="{{ old('kode_4', $editItem?->kode_4) }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="nama">Program Kelas</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $editItem?->nama) }}" required>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <label class="form-label" for="biaya_daftar">Biaya Daftar</label>
                                    <input type="number" class="form-control" id="biaya_daftar" name="biaya_daftar" value="{{ old('biaya_daftar', $editItem?->biaya_daftar) }}" required min="0">
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <label class="form-label" for="biaya_pendidikan">Biaya Pendidikan</label>
                                    <input type="number" class="form-control" id="biaya_pendidikan" name="biaya_pendidikan" value="{{ old('biaya_pendidikan', $editItem?->biaya_pendidikan) }}" required min="0">
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <label class="form-label" for="discount">Diskon</label>
                                    <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', $editItem?->discount ?? 0) }}" required min="0">
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <label class="form-label" for="siswa">Target Siswa</label>
                                    <input type="number" class="form-control" id="siswa" name="siswa" value="{{ old('siswa', $editItem?->siswa ?? 0) }}" required min="0">
                                </div>
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary">{{ $editItem ? 'Simpan Perubahan' : 'Simpan Produk' }}</button>
                                @if ($editItem)
                                    <a href="{{ route('produk.index', $parameterFilter) }}" class="btn btn-outline-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="row row-cards">
                @forelse ($grouped as $key => $groupItems)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">{{ $programs[$key] ?? $key }}</h3>
                                    @if (\App\Models\ProdukItem::programSubtitle($key))
                                        <div class="text-secondary small mt-1">{{ \App\Models\ProdukItem::programSubtitle($key) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table table-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Program</th>
                                            <th>Tahun</th>
                                            <th>Kode 1</th>
                                            <th>Kode 2</th>
                                            <th>Kode 3</th>
                                            <th>Kode 4</th>
                                            <th>Program Kelas</th>
                                            <th>Biaya Daftar</th>
                                            <th>Biaya Pendidikan</th>
                                            <th>Diskon</th>
                                            <th>Target Siswa</th>
                                            <th>Omzet</th>
                                            @if ($isSuperadmin)
                                                <th class="w-1">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupItems as $item)
                                            <tr>
                                                <td>{{ strtoupper($item->program) }}</td>
                                                <td>{{ $item->tahun_ajaran }}</td>
                                                <td>{{ $item->kode_1 }}</td>
                                                <td>{{ $item->kode_2 }}</td>
                                                <td>{{ $item->kode_3 }}</td>
                                                <td>{{ $item->kode_4 }}</td>
                                                <td class="text-wrap">{{ $item->nama }}</td>
                                                <td>Rp {{ number_format($item->biaya_daftar, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($item->biaya_pendidikan, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($item->discount, 0, ',', '.') }}</td>
                                                <td>{{ number_format($item->siswa, 0, ',', '.') }}</td>
                                                <td class="fw-semibold">Rp {{ number_format($item->omzet, 0, ',', '.') }}</td>
                                                @if ($isSuperadmin)
                                                    <td>
                                                        @php($parameterEdit = ['produk' => $item] + $parameterFilter)
                                                        <div class="btn-list justify-content-end flex-nowrap">
                                                            <a href="{{ route('produk.edit', $parameterEdit) }}" class="btn btn-outline-primary btn-sm">
                                                                Ubah
                                                            </a>
                                                            <form method="POST" action="{{ route('produk.destroy', $item) }}" onsubmit="return confirm('Hapus data produk ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="filter_tahun" value="{{ $selectedTahun }}">
                                                                <input type="hidden" name="filter_program" value="{{ $selectedProgram }}">
                                                                <input type="hidden" name="filter_kode" value="{{ $selectedKode }}">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="empty py-5">
                                    <p class="empty-title">Belum ada data produk</p>
                                    <p class="empty-subtitle text-secondary">
                                        Tambahkan data produk pertama atau ubah filter untuk melihat hasil lain.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        const programSelect = document.getElementById('program');
        const programCustomWrap = document.getElementById('program_custom_wrap');
        const programCustomInput = document.getElementById('program_custom');

        if (programSelect && programCustomWrap && programCustomInput) {
            const toggleProgramCustom = () => {
                const isCustom = programSelect.value === 'custom';
                programCustomWrap.classList.toggle('d-none', !isCustom);
                programCustomInput.required = isCustom;
            };

            programSelect.addEventListener('change', toggleProgramCustom);
            toggleProgramCustom();
        }
    </script>
@endsection
