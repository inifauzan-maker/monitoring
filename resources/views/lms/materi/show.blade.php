@extends('tataletak.aplikasi')

@section('judul_halaman', $materi->judul)
@section('pratitel_halaman', 'LMS')
@section('judul_konten', $materi->judul)
@section('deskripsi_halaman', 'Video tutorial materi LMS dengan sinkron progres otomatis berdasarkan durasi tonton.')

@section('aksi_halaman')
    <div class="btn-list">
        <a href="{{ route('lms.materi', ['kursus' => $materi->kursus_id]) }}" class="btn btn-outline-secondary">
            Kembali ke Materi
        </a>
        @if ($materiSebelumnya)
            <a href="{{ route('lms.materi.show', $materiSebelumnya) }}" class="btn btn-outline-secondary">
                Materi Sebelumnya
            </a>
        @endif
        @if ($materiBerikutnya)
            <a href="{{ route('lms.materi.show', $materiBerikutnya) }}" class="btn btn-primary">
                Materi Berikutnya
            </a>
        @endif
    </div>
@endsection

@section('konten')
    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold small">Video Tutorial</div>
                        <h3 class="card-title mt-1 mb-0">{{ $materi->judul }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark">
                        <div id="player"
                            data-youtube-id="{{ $materi->youtube_id }}"
                            data-progress-url="{{ route('lms.materi.progres', $materi) }}"
                            data-initial-second="{{ (int) $progres->detik_terakhir }}"
                            data-initial-percent="{{ (int) $progres->persen_progres }}">
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between small text-secondary mb-1">
                                <span id="materi-progress-label">{{ (int) $progres->persen_progres }}% selesai</span>
                                <span id="materi-status-label">{{ $progres->labelStatus() }}</span>
                            </div>
                            <div class="progress progress-sm">
                                <div id="materi-progress-bar" class="progress-bar bg-primary"
                                    style="width: {{ (int) $progres->persen_progres }}%"
                                    role="progressbar"
                                    aria-valuenow="{{ (int) $progres->persen_progres }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-blue-lt text-blue">{{ $materi->kursus?->judul }}</span>
                            <span class="badge bg-secondary-lt text-secondary">YouTube ID: {{ $materi->youtube_id }}</span>
                            <span class="badge bg-secondary-lt text-secondary">Durasi: {{ $materi->labelDurasi() }}</span>
                        </div>

                        <div class="alert alert-info mb-0">
                            <div class="fw-semibold mb-1">Sinkron progres otomatis aktif</div>
                            <div class="text-secondary">
                                Saat video berjalan, progres Anda akan disimpan otomatis berkala. Tombol selesai juga tersedia jika materi sudah tuntas.
                            </div>
                        </div>

                        <div class="btn-list">
                            <button type="button" id="tombol-selesai-materi" class="btn btn-success">
                                Tandai Selesai
                            </button>
                            @if ($materiBerikutnya)
                                <a href="{{ route('lms.materi.show', $materiBerikutnya) }}" class="btn btn-outline-primary">
                                    Lanjut ke Materi Berikutnya
                                </a>
                            @endif
                            <a href="{{ route('lms.progres_belajar', ['materi' => $materi->id]) }}" class="btn btn-outline-secondary">
                                Lihat di Progres Belajar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Kuis Terkait</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($kuisTersedia as $kuis)
                        @php($hasilKuis = $kuis->hasilPengguna->first())
                        <a href="{{ route('lms.kuis.show', $kuis) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $kuis->judul }}</div>
                                    <div class="small text-secondary">{{ $kuis->labelTarget() }} · {{ $kuis->labelSumberSoal() }} · {{ $kuis->jumlahSoalTampilEfektif() }} soal</div>
                                </div>
                                @if ($hasilKuis)
                                    <span class="badge {{ $hasilKuis->kelasBadgeHasil() }}">{{ $hasilKuis->skor }}%</span>
                                @else
                                    <span class="badge bg-secondary-lt text-secondary">Baru</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item">
                            <div class="text-secondary small">Belum ada kuis untuk materi atau kursus ini.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Kursus</h3>
                </div>
                <div class="card-body">
                    <div class="text-secondary text-uppercase fw-semibold small">Kursus</div>
                    <div class="h3 mt-2 mb-1">{{ $materi->kursus?->judul }}</div>
                    <div class="text-secondary mb-3">{{ $jumlahSelesai }} / {{ $jumlahMateri }} materi selesai</div>
                    <div class="progress progress-sm mb-2">
                        <div class="progress-bar bg-primary" style="width: {{ $persenKursus }}%"
                            role="progressbar" aria-valuenow="{{ $persenKursus }}" aria-valuemin="0"
                            aria-valuemax="100"></div>
                    </div>
                    <div class="small text-secondary">{{ $persenKursus }}% progres kursus</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Materi Kursus</h3>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($daftarMateri as $item)
                        @php($progresItem = $item->progresBelajar->first())
                        <a href="{{ route('lms.materi.show', $item) }}"
                            class="list-group-item list-group-item-action {{ $item->id === $materi->id ? 'active' : '' }}">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->urutan }}. {{ $item->judul }}</div>
                                    <div class="small {{ $item->id === $materi->id ? 'text-white-50' : 'text-secondary' }}">
                                        {{ $item->labelDurasi() }}
                                    </div>
                                </div>
                                <span class="badge {{ $item->id === $materi->id ? 'bg-white text-primary' : ($progresItem?->kelasBadgeStatus() ?? 'bg-secondary-lt text-secondary') }}">
                                    {{ $progresItem?->persen_progres ?? 0 }}%
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('skrip_halaman')
    <script>
        (() => {
            const playerEl = document.getElementById('player');
            const progressBar = document.getElementById('materi-progress-bar');
            const progressLabel = document.getElementById('materi-progress-label');
            const statusLabel = document.getElementById('materi-status-label');
            const tombolSelesai = document.getElementById('tombol-selesai-materi');

            if (!playerEl) {
                return;
            }

            const youtubeId = playerEl.dataset.youtubeId;
            const progressUrl = playerEl.dataset.progressUrl;
            const initialSecond = Number(playerEl.dataset.initialSecond || 0);
            const initialPercent = Number(playerEl.dataset.initialPercent || 0);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            let player = null;
            let lastSentSecond = initialSecond;
            let duration = 0;
            let intervalId = null;

            const updateUi = (percent, status) => {
                const nilai = Math.max(0, Math.min(100, Number(percent || 0)));

                if (progressBar) {
                    progressBar.style.width = `${nilai}%`;
                    progressBar.setAttribute('aria-valuenow', nilai);
                }

                if (progressLabel) {
                    progressLabel.textContent = `${nilai}% selesai`;
                }

                if (statusLabel && status) {
                    statusLabel.textContent = status;
                }
            };

            const kirimProgres = async (payload) => {
                try {
                    const response = await fetch(progressUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    updateUi(data.persen_progres, data.label_status);

                    if (typeof data.detik_terakhir === 'number') {
                        lastSentSecond = data.detik_terakhir;
                    }
                } catch (error) {
                    // Abaikan kegagalan sementara agar player tetap berjalan.
                }
            };

            const sinkronBerkala = () => {
                if (!player || typeof player.getCurrentTime !== 'function') {
                    return;
                }

                const currentTime = Math.floor(player.getCurrentTime());

                if (!duration && typeof player.getDuration === 'function') {
                    duration = Math.floor(player.getDuration());
                }

                if (currentTime - lastSentSecond < 8) {
                    return;
                }

                const persen = duration > 0
                    ? Math.min(100, Math.round((currentTime / duration) * 100))
                    : initialPercent;

                lastSentSecond = currentTime;
                kirimProgres({
                    detik_terakhir: currentTime,
                    persen_progres: persen,
                });
            };

            const siapkanPlayer = () => {
                if (!window.YT || !window.YT.Player || !youtubeId) {
                    return;
                }

                player = new window.YT.Player('player', {
                    videoId: youtubeId,
                    playerVars: {
                        rel: 0,
                        modestbranding: 1,
                    },
                    events: {
                        onReady: () => {
                            if (initialSecond > 0 && typeof player.seekTo === 'function') {
                                player.seekTo(initialSecond, true);
                            }

                            if (typeof player.getDuration === 'function') {
                                duration = Math.floor(player.getDuration());
                            }
                        },
                        onStateChange: (event) => {
                            if (event.data === window.YT.PlayerState.PLAYING && intervalId === null) {
                                intervalId = window.setInterval(sinkronBerkala, 9000);
                            }

                            if (
                                event.data === window.YT.PlayerState.PAUSED ||
                                event.data === window.YT.PlayerState.BUFFERING ||
                                event.data === window.YT.PlayerState.ENDED
                            ) {
                                sinkronBerkala();
                            }

                            if (event.data === window.YT.PlayerState.ENDED) {
                                kirimProgres({
                                    detik_terakhir: duration || lastSentSecond,
                                    persen_progres: 100,
                                    tandai_selesai: true,
                                });
                            }
                        },
                    },
                });
            };

            if (tombolSelesai) {
                tombolSelesai.addEventListener('click', () => {
                    kirimProgres({
                        detik_terakhir: duration || lastSentSecond,
                        persen_progres: 100,
                        tandai_selesai: true,
                    });
                });
            }

            updateUi(initialPercent, '{{ $progres->labelStatus() }}');

            if (window.YT && window.YT.Player) {
                siapkanPlayer();
            } else {
                const script = document.createElement('script');
                script.src = 'https://www.youtube.com/iframe_api';
                document.body.appendChild(script);
                window.onYouTubeIframeAPIReady = siapkanPlayer;
            }
        })();
    </script>
@endpush
