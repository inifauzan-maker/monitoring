<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class KampanyeController extends Controller
{
    private const HALAMAN = [
        'ads_iklan' => [
            'judul' => 'Ads/Iklan',
            'deskripsi' => 'Modul kampanye untuk pengelolaan iklan berbayar masih disiapkan.',
        ],
        'media_sosial' => [
            'judul' => 'Media Sosial',
            'deskripsi' => 'Modul kampanye untuk konten dan distribusi media sosial masih disiapkan.',
        ],
        'website' => [
            'judul' => 'Website',
            'deskripsi' => 'Modul kampanye untuk aktivitas website masih disiapkan.',
        ],
        'youtube' => [
            'judul' => 'Youtube',
            'deskripsi' => 'Modul kampanye untuk kanal Youtube masih disiapkan.',
        ],
        'event' => [
            'judul' => 'Event',
            'deskripsi' => 'Modul kampanye untuk kegiatan event masih disiapkan.',
        ],
        'buzzer' => [
            'judul' => 'Buzzer',
            'deskripsi' => 'Modul kampanye untuk aktivasi buzzer masih disiapkan.',
        ],
    ];

    public function adsIklan(): View
    {
        return $this->renderHalaman('ads_iklan');
    }

    public function mediaSosial(): View
    {
        return $this->renderHalaman('media_sosial');
    }

    public function website(): View
    {
        return $this->renderHalaman('website');
    }

    public function youtube(): View
    {
        return $this->renderHalaman('youtube');
    }

    public function event(): View
    {
        return $this->renderHalaman('event');
    }

    public function buzzer(): View
    {
        return $this->renderHalaman('buzzer');
    }

    private function renderHalaman(string $halaman): View
    {
        $dataHalaman = self::HALAMAN[$halaman];

        return view('kampanye.halaman_kosong', [
            'judulHalaman' => $dataHalaman['judul'],
            'deskripsiHalaman' => $dataHalaman['deskripsi'],
        ]);
    }
}
