<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LmsController extends Controller
{
    private const HALAMAN = [
        'kursus' => [
            'judul' => 'Kursus',
            'deskripsi' => 'Struktur LMS ini mengacu pada referensi kursus online untuk pengelolaan kelas dan katalog kursus.',
        ],
        'materi' => [
            'judul' => 'Materi',
            'deskripsi' => 'Halaman ini disiapkan untuk mengelola lesson atau video materi per kursus seperti pada referensi.',
        ],
        'playlist' => [
            'judul' => 'Playlist',
            'deskripsi' => 'Halaman ini disiapkan untuk import playlist YouTube ke dalam kursus seperti alur pada referensi.',
        ],
        'progres_belajar' => [
            'judul' => 'Progres Belajar',
            'deskripsi' => 'Halaman ini disiapkan untuk memantau progres belajar peserta berdasarkan materi yang sudah diselesaikan.',
        ],
    ];

    public function kursus(): View
    {
        return $this->renderHalaman('kursus');
    }

    public function materi(): View
    {
        return $this->renderHalaman('materi');
    }

    public function playlist(): View
    {
        return $this->renderHalaman('playlist');
    }

    public function progresBelajar(): View
    {
        return $this->renderHalaman('progres_belajar');
    }

    private function renderHalaman(string $halaman): View
    {
        $dataHalaman = self::HALAMAN[$halaman];

        return view('lms.halaman_kosong', [
            'judulHalaman' => $dataHalaman['judul'],
            'deskripsiHalaman' => $dataHalaman['deskripsi'],
        ]);
    }
}
