@extends('tataletak.aplikasi')

@section('judul_halaman', $kuis->judul)
@section('pratitel_halaman', 'LMS')
@section('judul_konten', $kuis->judul)
@section('deskripsi_halaman', 'Detail kuis LMS untuk target '.$kuis->labelTarget().' dengan hasil pengerjaan pengguna dan pengelolaan pertanyaan.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.kuis') }}" class="btn btn-outline-secondary">Kembali ke Kuis</a>
        @if ($kuis->untukMateri() && $kuis->materiKursus)
            <a href="{{ route('lms.materi.show', $kuis->materiKursus) }}" class="btn btn-outline-secondary">Buka Materi</a>
        @elseif ($kuis->kursus)
            <a href="{{ route('lms.kursus', ['q' => $kuis->kursus?->judul]) }}" class="btn btn-outline-secondary">Cari di Kursus</a>
        @endif
    </div>
@endsection

@section('konten')
    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Informasi Kuis</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-blue-lt text-blue">{{ $kuis->labelTarget() }}</span>
                        <span class="badge {{ $kuis->kelasBadgeAktif() }}">{{ $kuis->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        <span class="badge bg-secondary-lt text-secondary">{{ $kuis->labelSumberSoal() }}</span>
                        <span class="badge {{ $kuis->mengacakSoal() ? 'bg-indigo-lt text-indigo' : 'bg-secondary-lt text-secondary' }}">{{ $kuis->mengacakSoal() ? 'Randomisasi Aktif' : 'Urutan Tetap' }}</span>
                        <span class="badge {{ $kuis->mengacakOpsiJawaban() ? 'bg-purple-lt text-purple' : 'bg-secondary-lt text-secondary' }}">{{ $kuis->mengacakOpsiJawaban() ? 'Opsi Diacak' : 'Opsi Tetap' }}</span>
                        <span class="badge {{ $kuis->menggunakanTimer() ? 'bg-orange-lt text-orange' : 'bg-secondary-lt text-secondary' }}">{{ $kuis->labelDurasiTimer() }}</span>
                        <span class="badge {{ $kuis->membatasiPercobaan() ? 'bg-cyan-lt text-cyan' : 'bg-secondary-lt text-secondary' }}">{{ $kuis->labelMaksimalPercobaan() }}</span>
                        <span class="badge bg-secondary-lt text-secondary">Nilai lulus {{ $kuis->nilai_lulus }}%</span>
                        <span class="badge bg-secondary-lt text-secondary">{{ $totalSumberSoal }} sumber · tampil {{ $kuis->jumlahSoalTampilEfektif() }}</span>
                    </div>
                    <div class="fw-semibold mb-1">Target</div>
                    <div class="text-secondary mb-3">{{ $kuis->namaTarget() }}</div>
                    <div class="fw-semibold mb-1">Deskripsi</div>
                    <div class="text-secondary">{{ $kuis->deskripsi ?: 'Belum ada deskripsi kuis.' }}</div>
                </div>
            </div>

            @if ($hasilPengguna)
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Hasil Terakhir Anda</h3></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3"><div class="fw-semibold">{{ $hasilPengguna->skor }}%</div><div class="small text-secondary">Skor</div></div>
                            <div class="col-md-3"><div class="fw-semibold">{{ $hasilPengguna->jumlah_benar }}/{{ $hasilPengguna->jumlah_pertanyaan }}</div><div class="small text-secondary">Benar</div></div>
                            <div class="col-md-3"><div class="fw-semibold">{{ $hasilPengguna->jumlah_percobaan }}x</div><div class="small text-secondary">Percobaan</div></div>
                            <div class="col-md-3"><span class="badge {{ $hasilPengguna->kelasBadgeHasil() }}">{{ $hasilPengguna->labelHasil() }}</span><div class="small text-secondary mt-1">{{ $hasilPengguna->selesai_pada?->translatedFormat('d M Y H:i') }}</div></div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($reviewPercobaanTerakhir->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Review Percobaan Terakhir</h3></div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-4">
                            @foreach ($reviewPercobaanTerakhir as $item)
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div class="fw-semibold">{{ $item['urutan_tampil'] }}. {{ $item['pertanyaan'] }}</div>
                                        <span class="badge {{ $item['benar'] ? 'bg-green-lt text-green' : 'bg-red-lt text-red' }}">{{ $item['benar'] ? 'Benar' : 'Salah' }}</span>
                                    </div>
                                    <div class="small text-secondary mb-2">{{ $item['label_sumber'] }} · {{ $item['label_tingkat_kesulitan'] }}</div>
                                    <div class="small"><span class="fw-semibold">Pilihan Anda:</span> {{ $item['jawaban_pengguna'] }}</div>
                                    <div class="small"><span class="fw-semibold">Jawaban Benar:</span> {{ $item['jawaban_benar'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Kerjakan Kuis</h3></div>
                <div class="card-body">
                    @if ($statusPercobaanPengguna['batas_tercapai'])
                        <div class="alert alert-warning" role="alert">
                            <div class="fw-semibold mb-1">Batas percobaan sudah tercapai</div>
                            <div class="text-secondary">Anda sudah menggunakan {{ $statusPercobaanPengguna['jumlah'] }} dari {{ $statusPercobaanPengguna['maksimal'] }} percobaan yang tersedia untuk kuis ini.</div>
                        </div>
                    @elseif ($paketSoal->isEmpty())
                        <div class="empty py-4">
                            <p class="empty-title">Belum ada soal yang bisa ditampilkan</p>
                            <p class="empty-subtitle text-secondary mb-0">Superadmin perlu menambahkan pertanyaan manual atau menempelkan bank soal ke kuis ini.</p>
                        </div>
                    @elseif ($timerKuis && $timerKuis['sudah_habis'])
                        <div class="alert alert-warning" role="alert">
                            <div class="fw-semibold mb-1">Waktu pengerjaan sudah habis</div>
                            <div class="text-secondary">Percobaan ini tidak bisa dilanjutkan. Mulai ulang untuk mendapatkan sesi timer baru.</div>
                        </div>

                        <form method="POST" action="{{ route('lms.kuis.restart', $kuis) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Mulai Ulang Percobaan</button>
                        </form>
                    @else
                        @if ($timerKuis)
                            <div class="alert alert-info mb-4" role="alert">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <div class="fw-semibold mb-1">Timer Kuis Aktif</div>
                                        <div class="text-secondary">Jawaban akan dikirim otomatis saat waktu habis. Jawaban yang belum terisi akan dihitung salah.</div>
                                    </div>
                                    <div class="text-end" data-kuis-timer-wrapper>
                                        <div class="small text-secondary text-uppercase fw-semibold">Sisa Waktu</div>
                                        <div class="fs-1 fw-bold lh-1" data-kuis-timer
                                            data-target="{{ $timerKuis['berakhir_pada']->toIso8601String() }}">{{ gmdate('i:s', $timerKuis['sisa_detik']) }}</div>
                                        <div class="small text-secondary mt-1">Mulai {{ $timerKuis['dimulai_pada']->translatedFormat('d M Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('lms.kuis.submit', $kuis) }}" data-form-kuis>
                            @csrf
                            <input type="hidden" name="submit_otomatis" value="0" data-submit-otomatis-input>
                            <div class="d-flex flex-column gap-4">
                                @foreach ($paketSoal as $item)
                                    <div class="border rounded p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div class="fw-semibold">{{ $item['urutan_tampil'] }}. {{ $item['pertanyaan'] }}</div>
                                            <div class="d-flex flex-column align-items-end gap-1">
                                                <span class="badge bg-secondary-lt text-secondary">{{ $item['label_sumber'] }}</span>
                                                @if ($item['tingkat_kesulitan'])
                                                    <span class="badge {{ $item['tingkat_kesulitan'] === 'mudah' ? 'bg-green-lt text-green' : ($item['tingkat_kesulitan'] === 'sulit' ? 'bg-red-lt text-red' : 'bg-yellow-lt text-yellow') }}">{{ $item['label_tingkat_kesulitan'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach ($item['opsi_jawaban'] as $indeks => $opsi)
                                                <label class="form-check">
                                                    <input class="form-check-input" type="radio" name="jawaban[{{ $item['kode'] }}]" value="{{ $indeks }}"
                                                        @checked((int) old('jawaban.'.$item['kode'], -1) === $indeks)>
                                                    <span class="form-check-label">{{ $opsi }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 btn-list">
                                <button type="submit" class="btn btn-primary" data-tombol-submit-kuis>Kirim Jawaban</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Status Percobaan Anda</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="fw-semibold">{{ $statusPercobaanPengguna['jumlah'] }}</div>
                            <div class="small text-secondary">Dipakai</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">
                                @if ($statusPercobaanPengguna['maksimal'] !== null)
                                    {{ $statusPercobaanPengguna['sisa'] }}
                                @else
                                    Tak terbatas
                                @endif
                            </div>
                            <div class="small text-secondary">Sisa</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">
                                @if ($statusPercobaanPengguna['maksimal'] !== null)
                                    {{ $statusPercobaanPengguna['maksimal'] }}
                                @else
                                    Fleksibel
                                @endif
                            </div>
                            <div class="small text-secondary">Batas</div>
                        </div>
                    </div>

                    @if ($statusPercobaanPengguna['maksimal'] !== null)
                        <div class="small text-secondary mt-3">Percobaan berikutnya akan menjadi ke-{{ $statusPercobaanPengguna['percobaan_berikutnya'] }}.</div>
                    @endif
                </div>
            </div>

            @if ($leaderboardKuis['items']->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Leaderboard</h3></div>
                    <div class="list-group list-group-flush">
                        @foreach ($leaderboardKuis['items'] as $item)
                            <div class="list-group-item {{ $item['adalah_pengguna_aktif'] ? 'bg-primary-lt' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">#{{ $item['peringkat'] }} {{ $item['nama'] }}</div>
                                        <div class="small text-secondary">{{ $item['jumlah_benar'] }}/{{ $item['jumlah_pertanyaan'] }} benar · {{ $item['total_percobaan'] }}x percobaan</div>
                                        <div class="small text-secondary">Percobaan terbaik ke-{{ $item['percobaan_terbaik'] }} · {{ $item['selesai_pada']?->translatedFormat('d M Y H:i') }}</div>
                                    </div>
                                    <span class="badge bg-blue-lt text-blue">{{ $item['skor'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($leaderboardKuis['peringkat_pengguna'] && $leaderboardKuis['peringkat_pengguna']['peringkat'] > $leaderboardKuis['items']->count())
                        <div class="card-footer">
                            <div class="small text-secondary">Peringkat Anda saat ini: #{{ $leaderboardKuis['peringkat_pengguna']['peringkat'] }} dari {{ $leaderboardKuis['total_peserta'] }} peserta.</div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($analitikKuis)
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Analitik Kuis</h3></div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="fw-semibold">{{ $analitikKuis['total_percobaan'] }}</div>
                                <div class="small text-secondary">Percobaan</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-semibold">{{ $analitikKuis['rata_rata_skor'] }}%</div>
                                <div class="small text-secondary">Rata-rata</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-semibold">{{ $analitikKuis['tingkat_lulus'] }}%</div>
                                <div class="small text-secondary">Lulus</div>
                            </div>
                        </div>

                        <div class="fw-semibold mb-2">Akurasi Per Kesulitan</div>
                        <div class="d-flex flex-column gap-2">
                            @forelse ($analitikKuis['detail_kesulitan'] as $item)
                                <div class="border rounded p-2">
                                    <div class="d-flex justify-content-between gap-3">
                                        <span class="small fw-semibold">{{ $item['label'] }}</span>
                                        <span class="small text-secondary">{{ $item['akurasi'] }}%</span>
                                    </div>
                                    <div class="small text-secondary">{{ $item['benar'] }} benar dari {{ $item['total'] }} jawaban</div>
                                </div>
                            @empty
                                <div class="small text-secondary">Belum ada data percobaan untuk dianalisis.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if ($isSuperadmin)
                @if ($kuis->menggunakanBankSoal())
                    <div class="card mb-3" id="form-bank-soal">
                        <div class="card-header">
                            <h3 class="card-title">Tempelkan Bank Soal</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('lms.kuis.bank_soal.store', $kuis) }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="bank_soal_lms_id">Bank Soal</label>
                                        <select class="form-select" id="bank_soal_lms_id" name="bank_soal_lms_id" required>
                                            <option value="">Pilih bank soal</option>
                                            @foreach ($bankSoalTersedia as $item)
                                                <option value="{{ $item->id }}">{{ $item->kursus?->judul ?? 'Global' }} - {{ $item->labelTingkatKesulitan() }} - {{ \Illuminate\Support\Str::limit($item->pertanyaan, 70) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="urutan_bank_soal">Urutan</label>
                                        <input type="number" class="form-control" id="urutan_bank_soal" name="urutan" min="1" placeholder="Otomatis di akhir">
                                    </div>
                                </div>
                                <div class="mt-4 btn-list">
                                    <button type="submit" class="btn btn-primary">Tempelkan ke Kuis</button>
                                    <a href="{{ route('lms.bank_soal') }}" class="btn btn-outline-secondary">Kelola Bank Soal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card mb-3" id="form-pertanyaan">
                        <div class="card-header">
                            <h3 class="card-title">{{ $editPertanyaan ? 'Ubah Pertanyaan' : 'Tambah Pertanyaan' }}</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ $editPertanyaan ? route('lms.kuis.pertanyaan.update', [$kuis, $editPertanyaan]) : route('lms.kuis.pertanyaan.store', $kuis) }}">
                                @csrf
                                @if ($editPertanyaan)
                                    @method('PUT')
                                @endif
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="pertanyaan">Pertanyaan</label>
                                        <textarea class="form-control" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan', $editPertanyaan?->pertanyaan) }}</textarea>
                                    </div>
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="col-12">
                                            <label class="form-label" for="opsi_{{ $i + 1 }}">Opsi {{ $i + 1 }}</label>
                                            <input type="text" class="form-control" id="opsi_{{ $i + 1 }}" name="opsi_{{ $i + 1 }}"
                                                value="{{ old('opsi_'.($i + 1), $editPertanyaan?->opsi_jawaban[$i] ?? '') }}" required>
                                        </div>
                                    @endfor
                                    <div class="col-md-6">
                                        <label class="form-label" for="indeks_jawaban_benar">Jawaban Benar</label>
                                        <select class="form-select" id="indeks_jawaban_benar" name="indeks_jawaban_benar" required>
                                            @for ($i = 0; $i < 4; $i++)
                                                <option value="{{ $i }}" @selected((int) old('indeks_jawaban_benar', $editPertanyaan?->indeks_jawaban_benar ?? 0) === $i)>Opsi {{ $i + 1 }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="urutan">Urutan</label>
                                        <input type="number" class="form-control" id="urutan" name="urutan" min="1" value="{{ old('urutan', $editPertanyaan?->urutan) }}">
                                    </div>
                                </div>
                                <div class="mt-4 btn-list">
                                    <button type="submit" class="btn btn-primary">{{ $editPertanyaan ? 'Simpan Perubahan' : 'Tambah Pertanyaan' }}</button>
                                    @if ($editPertanyaan)
                                        <a href="{{ route('lms.kuis.show', $kuis) }}" class="btn btn-outline-secondary">Batal</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ $kuis->menggunakanBankSoal() ? 'Bank Soal Terpasang' : 'Daftar Pertanyaan' }}</h3></div>
                <div class="list-group list-group-flush">
                    @if ($kuis->menggunakanBankSoal())
                        @forelse ($kuis->bankSoal as $item)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $item->pivot->urutan }}. {{ $item->pertanyaan }}</div>
                                        <div class="small text-secondary mt-2">Jawaban benar: {{ $item->jawabanBenar() }}</div>
                                        <div class="small text-secondary">{{ $item->kursus?->judul ?? 'Global' }}</div>
                                        <div class="small text-secondary">{{ $item->labelTingkatKesulitan() }}</div>
                                    </div>
                                    @if ($isSuperadmin)
                                        <form method="POST" action="{{ route('lms.kuis.bank_soal.destroy', [$kuis, $item]) }}" onsubmit="return confirm('Lepas bank soal ini dari kuis?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Lepas</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">
                                <div class="text-secondary small">Belum ada bank soal yang ditempelkan ke kuis ini.</div>
                            </div>
                        @endforelse
                    @else
                        @forelse ($kuis->pertanyaan as $item)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $item->urutan }}. {{ $item->pertanyaan }}</div>
                                        <div class="small text-secondary mt-2">Jawaban benar: {{ $item->jawabanBenar() }}</div>
                                    </div>
                                    @if ($isSuperadmin)
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('lms.kuis.show', ['kuisLms' => $kuis, 'pertanyaan' => $item->id]) }}#form-pertanyaan" class="btn btn-outline-primary btn-sm">Ubah</a>
                                            <form method="POST" action="{{ route('lms.kuis.pertanyaan.destroy', [$kuis, $item]) }}" onsubmit="return confirm('Hapus pertanyaan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">
                                <div class="text-secondary small">Belum ada pertanyaan untuk kuis ini.</div>
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>

            @if ($riwayatPercobaan->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Riwayat Percobaan Anda</h3></div>
                    <div class="list-group list-group-flush">
                        @foreach ($riwayatPercobaan as $percobaan)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">Percobaan ke-{{ $percobaan->percobaan_ke }}</div>
                                        <div class="small text-secondary">{{ $percobaan->jumlah_benar }}/{{ $percobaan->jumlah_pertanyaan }} benar · skor {{ $percobaan->skor }}%</div>
                                        <div class="small text-secondary">{{ $percobaan->selesai_pada?->translatedFormat('d M Y H:i') }}</div>
                                    </div>
                                    <span class="badge {{ $percobaan->kelasBadgeHasil() }}">{{ $percobaan->labelHasil() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($timerKuis && ! $timerKuis['sudah_habis'] && $paketSoal->isNotEmpty())
    @push('skrip_halaman')
        <script>
            (() => {
                const tampilanTimer = document.querySelector('[data-kuis-timer]');
                const formKuis = document.querySelector('[data-form-kuis]');
                const inputSubmitOtomatis = document.querySelector('[data-submit-otomatis-input]');
                const tombolSubmit = document.querySelector('[data-tombol-submit-kuis]');

                if (! tampilanTimer || ! formKuis || ! inputSubmitOtomatis) {
                    return;
                }

                const target = new Date(tampilanTimer.dataset.target ?? '').getTime();

                if (Number.isNaN(target)) {
                    return;
                }

                let intervalTimer = null;
                let sudahSubmit = false;

                const formatSisaWaktu = (totalDetik) => {
                    const jam = Math.floor(totalDetik / 3600);
                    const menit = Math.floor((totalDetik % 3600) / 60);
                    const detik = totalDetik % 60;

                    if (jam > 0) {
                        return `${String(jam).padStart(2, '0')}:${String(menit).padStart(2, '0')}:${String(detik).padStart(2, '0')}`;
                    }

                    return `${String(menit).padStart(2, '0')}:${String(detik).padStart(2, '0')}`;
                };

                const kirimOtomatis = () => {
                    if (sudahSubmit) {
                        return;
                    }

                    sudahSubmit = true;
                    inputSubmitOtomatis.value = '1';

                    if (tombolSubmit) {
                        tombolSubmit.disabled = true;
                        tombolSubmit.textContent = 'Mengirim Otomatis...';
                    }

                    formKuis.requestSubmit();
                };

                const perbaruiTimer = () => {
                    const sekarang = Date.now();
                    const sisaDetik = Math.max(0, Math.floor((target - sekarang) / 1000));

                    tampilanTimer.textContent = formatSisaWaktu(sisaDetik);

                    if (sisaDetik === 0) {
                        window.clearInterval(intervalTimer);
                        kirimOtomatis();
                    }
                };

                formKuis.addEventListener('submit', () => {
                    sudahSubmit = true;
                    window.clearInterval(intervalTimer);
                }, { once: true });

                perbaruiTimer();
                intervalTimer = window.setInterval(perbaruiTimer, 1000);
            })();
        </script>
    @endpush
@endif
