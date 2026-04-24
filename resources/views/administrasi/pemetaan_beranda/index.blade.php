@extends('tataletak.aplikasi')

@section('judul_halaman', 'Pemetaan Beranda')
@section('pratitel_halaman', 'Administrasi')
@section('judul_konten', 'Pemetaan Beranda')
@section('deskripsi_halaman', 'Atur profil beranda yang dipakai tiap level akses agar dashboard mengikuti fungsi kerja tim Anda.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('dashboard.beranda') }}" class="btn btn-outline-secondary">
            @include('komponen.ikon_menu', ['ikon' => 'beranda'])
            <span>Lihat Beranda</span>
        </a>
    </div>
@endsection

@section('konten')
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pemetaan Level ke Profil Beranda</h3>
                </div>
                <div class="card-body border-bottom">
                    <div class="alert alert-info mb-4">
                        Ubah pilihan profil untuk melihat preview fokus kerja, indikator utama, dan jalur cepat sebelum pemetaan disimpan.
                    </div>
                    <div class="row g-3">
                        @foreach ($opsiProfilBeranda as $profil)
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold">{{ $profil['label'] }}</div>
                                    <div class="text-secondary small mt-1">{{ $profil['keterangan'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <form method="POST" action="{{ route('administrasi.pemetaan_beranda.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th style="width: 16rem;">Level Akses</th>
                                    <th style="width: 20rem;">Profil Beranda Aktif</th>
                                    <th style="min-width: 24rem;">Preview Profil</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    @php
                                        $namaInput = 'profil.' . $item['level_akses']->value;
                                        $profilTerpilih = old($namaInput, $item['profil_aktif']->value);
                                        $pratinjauAktif = $opsiProfilBeranda[$profilTerpilih]['pratinjau'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['level_akses']->label() }}</div>
                                            <div class="text-secondary small">Default bawaan: {{ $item['profil_bawaan']->label() }}</div>
                                        </td>
                                        <td>
                                            <select class="form-select @error($namaInput) is-invalid @enderror"
                                                name="profil[{{ $item['level_akses']->value }}]"
                                                data-pemetaan-profil-select>
                                                @foreach ($opsiProfilBeranda as $nilai => $profil)
                                                    <option value="{{ $nilai }}" @selected($profilTerpilih === $nilai)>
                                                        {{ $profil['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-secondary small mt-2" data-pratinjau-label>
                                                {{ $opsiProfilBeranda[$profilTerpilih]['label'] }}
                                            </div>
                                            @error($namaInput)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <div class="border rounded-3 p-3 bg-body-tertiary">
                                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                    <div>
                                                        <div class="fw-semibold" data-pratinjau-judul>{{ $pratinjauAktif['judul'] }}</div>
                                                        <div class="text-secondary small mt-1" data-pratinjau-keterangan>
                                                            {{ $opsiProfilBeranda[$profilTerpilih]['keterangan'] }}
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-azure-lt text-azure" data-pratinjau-badge>{{ $pratinjauAktif['badge'] }}</span>
                                                </div>

                                                <div class="row g-3 mt-1">
                                                    <div class="col-md-6">
                                                        <div class="text-secondary text-uppercase small">Indikator utama</div>
                                                        <div class="fw-medium mt-1" data-pratinjau-indikator>{{ $pratinjauAktif['indikator'] }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="text-secondary text-uppercase small">Jalur cepat</div>
                                                        <div class="fw-medium mt-1" data-pratinjau-jalur-cepat>{{ $pratinjauAktif['jalur_cepat'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="text-secondary text-uppercase small mt-3">Fokus utama</div>
                                                <div class="d-flex flex-wrap gap-2 mt-2" data-pratinjau-fokus>
                                                    @foreach ($pratinjauAktif['fokus'] as $fokus)
                                                        <span class="badge bg-secondary-lt">{{ $fokus }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div class="text-secondary small">
                            Perubahan ini hanya mengubah tampilan dan fokus beranda per level. Hak akses modul tetap mengikuti RBAC yang sudah berlaku.
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Pemetaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('skrip_halaman')
    <script id="data-pratinjau-profil-beranda" type="application/json">
        {!! json_encode($opsiProfilBeranda, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const elemenData = document.getElementById('data-pratinjau-profil-beranda');

            if (!elemenData) {
                return;
            }

            const opsiProfilBeranda = JSON.parse(elemenData.textContent);

            document.querySelectorAll('[data-pemetaan-profil-select]').forEach((selectElement) => {
                const baris = selectElement.closest('tr');

                if (!baris) {
                    return;
                }

                const renderPratinjau = () => {
                    const profil = opsiProfilBeranda[selectElement.value];

                    if (!profil) {
                        return;
                    }

                    const pratinjau = profil.pratinjau ?? {};
                    const targetFokus = baris.querySelector('[data-pratinjau-fokus]');

                    baris.querySelector('[data-pratinjau-label]').textContent = profil.label ?? '';
                    baris.querySelector('[data-pratinjau-judul]').textContent = pratinjau.judul ?? '';
                    baris.querySelector('[data-pratinjau-keterangan]').textContent = profil.keterangan ?? '';
                    baris.querySelector('[data-pratinjau-badge]').textContent = pratinjau.badge ?? '';
                    baris.querySelector('[data-pratinjau-indikator]').textContent = pratinjau.indikator ?? '';
                    baris.querySelector('[data-pratinjau-jalur-cepat]').textContent = pratinjau.jalur_cepat ?? '';

                    if (targetFokus) {
                        targetFokus.innerHTML = '';

                        (pratinjau.fokus ?? []).forEach((itemFokus) => {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-secondary-lt';
                            badge.textContent = itemFokus;
                            targetFokus.appendChild(badge);
                        });
                    }
                };

                selectElement.addEventListener('change', renderPratinjau);
                renderPratinjau();
            });
        });
    </script>
@endpush
