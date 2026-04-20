<?php

namespace App\Http\Controllers;

use App\Models\KategoriArtikel;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KategoriArtikelController extends Controller
{
    public function index(): View
    {
        return view('tools.artikel.kategori', [
            'items' => KategoriArtikel::query()
                ->withCount('artikel')
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('nama'))),
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('kategori_artikel', 'slug')],
            'deskripsi' => ['nullable', 'string'],
        ], [], [
            'nama' => 'nama kategori',
            'slug' => 'slug',
            'deskripsi' => 'deskripsi',
        ]);

        $kategoriArtikel = KategoriArtikel::create($data);

        PencatatLogAktivitas::catat(
            $request,
            'artikel',
            'tambah',
            'Menambahkan kategori artikel.',
            $kategoriArtikel,
            [
                'nama' => $kategoriArtikel->nama,
                'slug' => $kategoriArtikel->slug,
            ]
        );

        return back()->with('status', 'Kategori artikel berhasil ditambahkan.');
    }

    public function destroy(KategoriArtikel $kategoriArtikel): RedirectResponse
    {
        if ($kategoriArtikel->artikel()->exists()) {
            return back()->withErrors([
                'kategori' => 'Kategori masih dipakai oleh artikel dan belum bisa dihapus.',
            ]);
        }

        PencatatLogAktivitas::catat(
            request(),
            'artikel',
            'hapus',
            'Menghapus kategori artikel.',
            $kategoriArtikel,
            [
                'nama' => $kategoriArtikel->nama,
                'slug' => $kategoriArtikel->slug,
            ]
        );

        $kategoriArtikel->delete();

        return back()->with('status', 'Kategori artikel berhasil dihapus.');
    }
}
