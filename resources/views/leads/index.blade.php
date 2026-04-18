@extends('tataletak.aplikasi')

@section('judul_halaman', 'Leads')
@section('pratitel_halaman', 'Modul')
@section('judul_konten', 'Leads')
@section('deskripsi_halaman', 'Pipeline prospek, input lead baru, dan tindak lanjut harian.')

@section('konten')
    @php
        $totalLeads = array_sum($funnel);
        $maxFunnel = max(1, $funnel['prospek'], $funnel['follow_up'], $funnel['closing'], $funnel['batal']);
    @endphp

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Total Prospek</div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalLeads, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Prospek</div>
                    <div class="h1 mb-0 mt-2 text-primary">{{ number_format($funnel['prospek'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Tindak Lanjut</div>
                    <div class="h1 mb-0 mt-2 text-orange">{{ number_format($funnel['follow_up'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Berhasil</div>
                    <div class="h1 mb-0 mt-2 text-green">{{ number_format($funnel['closing'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Prospek</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="filter_channel">Kanal</label>
                            <select class="form-select" name="channel" id="filter_channel">
                                <option value="">Semua Kanal</option>
                                @foreach ($channels as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedChannel === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="filter_status">Status</label>
                            <select class="form-select" name="status" id="filter_status">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-list">
                                <button type="submit" class="btn btn-primary">Terapkan</button>
                                @if ($selectedChannel || $selectedStatus)
                                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Atur Ulang</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Tahapan Prospek</h3>
                </div>
                <div class="card-body">
                    @foreach ([
                        ['label' => 'Prospek', 'value' => $funnel['prospek'], 'class' => 'bg-primary'],
                        ['label' => 'Tindak Lanjut', 'value' => $funnel['follow_up'], 'class' => 'bg-orange'],
                        ['label' => 'Berhasil', 'value' => $funnel['closing'], 'class' => 'bg-green'],
                        ['label' => 'Batal', 'value' => $funnel['batal'], 'class' => 'bg-secondary'],
                    ] as $row)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $row['label'] }}</span>
                                <span class="fw-semibold">{{ number_format($row['value'], 0, ',', '.') }}</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar {{ $row['class'] }}" style="width: {{ ($row['value'] / $maxFunnel) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Tambah Prospek</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('leads.store') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label" for="nama_siswa">Nama Siswa</label>
                            <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" value="{{ old('nama_siswa') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="asal_sekolah">Asal Sekolah</label>
                            <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" value="{{ old('asal_sekolah') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="nomor_telepon">Nomor Telepon</label>
                            <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="channel">Kanal</label>
                            <select class="form-select" id="channel" name="channel">
                                <option value="">Pilih Kanal</option>
                                @foreach ($channels as $key => $label)
                                    <option value="{{ $key }}" @selected(old('channel') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', 'prospek') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sumber">Sumber</label>
                            <input type="text" class="form-control" id="sumber" name="sumber" value="{{ old('sumber') }}" placeholder="Ads/Iklan, Referensi, Organik">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pic_id">Penanggung Jawab</label>
                            <select class="form-select" id="pic_id" name="pic_id">
                                <option value="">Pilih Penanggung Jawab</option>
                                @foreach ($daftarPic as $pic)
                                    <option value="{{ $pic->id }}" @selected((string) old('pic_id') === (string) $pic->id)>{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="jadwal_tindak_lanjut">Jadwal Tindak Lanjut</label>
                            <input type="datetime-local" class="form-control" id="jadwal_tindak_lanjut" name="jadwal_tindak_lanjut" value="{{ old('jadwal_tindak_lanjut') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="catatan">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Simpan Prospek</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Catat Tindak Lanjut</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('leads.tindak-lanjut.store') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="lead_id">Pilih Prospek</label>
                            <select class="form-select" id="lead_id" name="lead_id" required>
                                <option value="">Pilih Prospek</option>
                                @foreach ($leadOptions as $leadOption)
                                    <option value="{{ $leadOption->id }}">{{ $leadOption->nama_siswa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="status_tindak_lanjut">Status Tindak Lanjut</label>
                            <select class="form-select" id="status_tindak_lanjut" name="status" required>
                                @foreach ($statusTindakLanjut as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="jadwal_tindak_lanjut_baru">Jadwal</label>
                            <input type="datetime-local" class="form-control" id="jadwal_tindak_lanjut_baru" name="jadwal_tindak_lanjut">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="catatan_tindak_lanjut">Catatan</label>
                            <textarea class="form-control" id="catatan_tindak_lanjut" name="catatan" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">Simpan Tindak Lanjut</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Pengingat Hari Ini</h3>
                </div>
                <div class="card-body">
                    @forelse ($pengingat as $item)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->lead?->nama_siswa }}</div>
                                    <div class="text-secondary small">{{ $item->lead?->asal_sekolah ?: 'Sekolah belum diisi' }}</div>
                                </div>
                                <div class="text-secondary small">
                                    {{ $item->jadwal_tindak_lanjut?->format('d M Y H:i') ?: '-' }}
                                </div>
                            </div>
                            <div class="mt-2">{{ $item->catatan ?: 'Belum ada catatan tambahan.' }}</div>
                        </div>
                    @empty
                        <div class="empty py-5">
                            <p class="empty-title">Belum ada pengingat</p>
                            <p class="empty-subtitle text-secondary">Tindak lanjut yang mendekati jadwal akan tampil di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Prospek</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Sekolah</th>
                                <th>Kanal</th>
                                <th>Status</th>
                                <th>Jadwal</th>
                                <th>Penanggung Jawab</th>
                                <th>Tindak Lanjut</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leads as $lead)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $lead->nama_siswa }}</div>
                                        <div class="text-secondary small">{{ $lead->nomor_telepon ?: '-' }}</div>
                                    </td>
                                    <td>{{ $lead->asal_sekolah ?: '-' }}</td>
                                    <td>
                                        <div>{{ $lead->channel ?: '-' }}</div>
                                        <div class="text-secondary small">{{ $lead->sumber ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $lead->kelasBadgeStatus() }}">{{ $lead->labelStatus() }}</span>
                                    </td>
                                    <td>{{ $lead->jadwal_tindak_lanjut?->format('d M Y H:i') ?: '-' }}</td>
                                    <td>{{ $lead->pic?->name ?: '-' }}</td>
                                    <td>{{ number_format($lead->tindak_lanjut_count, 0, ',', '.') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('leads.status', $lead) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                @foreach ($statuses as $key => $label)
                                                    <option value="{{ $key }}" @selected($lead->status === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary">Belum ada data prospek.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
