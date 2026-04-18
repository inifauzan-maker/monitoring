<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ToolsController extends Controller
{
    private const HALAMAN = [
        'link' => [
            'judul' => 'Link',
            'deskripsi' => 'Modul tools untuk pengelolaan link masih disiapkan.',
        ],
    ];

    public function link(): View
    {
        return $this->renderHalaman('link');
    }

    private function renderHalaman(string $halaman): View
    {
        $dataHalaman = self::HALAMAN[$halaman];

        return view('tools.halaman_kosong', [
            'judulHalaman' => $dataHalaman['judul'],
            'deskripsiHalaman' => $dataHalaman['deskripsi'],
        ]);
    }
}
