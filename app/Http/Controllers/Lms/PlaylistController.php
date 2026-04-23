<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use App\Models\MateriKursus;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlaylistController extends Controller
{
    public function index(): View
    {
        return view('lms.playlist.index', [
            'opsiKursus' => Kursus::query()->orderBy('judul')->get(['id', 'judul']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $validated = $request->validate([
            'kursus_id' => ['required', 'integer', 'exists:kursus,id'],
            'playlist_url' => ['required', 'string'],
            'durasi_default_detik' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'kursus_id' => 'kursus',
            'playlist_url' => 'URL / ID playlist',
            'durasi_default_detik' => 'durasi default detik',
        ]);

        $playlistId = $this->extractPlaylistId((string) $validated['playlist_url']);

        if (! $playlistId) {
            return back()->withErrors([
                'playlist_url' => 'Playlist ID tidak ditemukan dari URL yang diberikan.',
            ])->withInput();
        }

        $apiKey = config('services.youtube.api_key');

        if (! $apiKey) {
            return back()->withErrors([
                'playlist_url' => 'Isi YOUTUBE_API_KEY di file .env untuk mengaktifkan import playlist.',
            ])->withInput();
        }

        $kursus = Kursus::query()->findOrFail($validated['kursus_id']);
        $durasiDefault = (int) ($validated['durasi_default_detik'] ?? 0);
        $jumlahDibuat = 0;
        $jumlahDuplikat = 0;
        $pageToken = null;
        $urutan = ((int) $kursus->materiKursus()->max('urutan')) + 1;

        do {
            $response = Http::get('https://www.googleapis.com/youtube/v3/playlistItems', [
                'part' => 'snippet,contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'pageToken' => $pageToken,
                'key' => $apiKey,
            ]);

            if (! $response->ok()) {
                return back()->withErrors([
                    'playlist_url' => 'Gagal mengambil data playlist dari YouTube.',
                ])->withInput();
            }

            $payload = $response->json();
            $items = $payload['items'] ?? [];

            foreach ($items as $item) {
                $youtubeId = $item['contentDetails']['videoId'] ?? null;
                $judul = $item['snippet']['title'] ?? null;

                if (! filled($youtubeId) || ! filled($judul) || $judul === 'Deleted video' || $judul === 'Private video') {
                    continue;
                }

                $sudahAda = $kursus->materiKursus()
                    ->where('youtube_id', $youtubeId)
                    ->exists();

                if ($sudahAda) {
                    $jumlahDuplikat++;
                    continue;
                }

                MateriKursus::query()->create([
                    'kursus_id' => $kursus->id,
                    'judul' => trim((string) $judul),
                    'youtube_id' => trim((string) $youtubeId),
                    'durasi_detik' => $durasiDefault,
                    'urutan' => $urutan,
                ]);

                $urutan++;
                $jumlahDibuat++;
            }

            $pageToken = $payload['nextPageToken'] ?? null;
        } while ($pageToken);

        PencatatLogAktivitas::catat(
            $request,
            'playlist',
            'impor',
            'Mengimpor playlist YouTube ke materi kursus.',
            $kursus,
            [
                'kursus_id' => $kursus->id,
                'kursus' => $kursus->judul,
                'playlist_id' => $playlistId,
                'jumlah_dibuat' => $jumlahDibuat,
                'jumlah_duplikat' => $jumlahDuplikat,
                'durasi_default_detik' => $durasiDefault,
            ],
        );

        return redirect()
            ->route('lms.materi', ['kursus' => $kursus->id])
            ->with('status', "Import playlist selesai. {$jumlahDibuat} materi baru ditambahkan, {$jumlahDuplikat} dilewati.");
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->adalahSuperadmin(), 403, 'Akses ditolak.');
    }

    private function extractPlaylistId(string $input): ?string
    {
        if (preg_match('/[?&]list=([a-zA-Z0-9_-]+)/', $input, $matches)) {
            return $matches[1];
        }

        $trimmed = trim($input);

        if (preg_match('/^([a-zA-Z0-9_-]+)$/', $trimmed)) {
            return $trimmed;
        }

        return null;
    }
}
