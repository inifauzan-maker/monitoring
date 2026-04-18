<?php

use App\Http\Controllers\Autentikasi\SesiController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\KampanyeController;
use App\Http\Controllers\KategoriArtikelController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LinkAnalitikExportController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LinkPublikController;
use App\Http\Controllers\OmzetController;
use App\Http\Controllers\Pengaturan\PenggunaController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [SesiController::class, 'create'])->name('login');
    Route::post('/masuk', [SesiController::class, 'store'])->name('sesi.store');
});

Route::get('/', [LinkPublikController::class, 'root'])->name('dashboard.beranda');
Route::get('/cta', [LinkPublikController::class, 'domainCta'])->name('publik.domain_link.cta');
Route::get('/link/{linkPengguna}', [LinkPublikController::class, 'domainBuka'])->name('publik.domain_link.buka');
Route::get('/u/{pengguna:slug_link}', [LinkPublikController::class, 'show'])->name('publik.link.show');
Route::get('/u/{pengguna:slug_link}/cta', [LinkPublikController::class, 'cta'])->name('publik.link.cta');
Route::get('/u/{pengguna:slug_link}/link/{linkPengguna}', [LinkPublikController::class, 'buka'])->name('publik.link.buka');

Route::middleware('auth')->group(function () {
    Route::get('/omzet', [OmzetController::class, 'index'])->name('omzet.index');
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::prefix('kampanye')
        ->name('kampanye.')
        ->group(function () {
            Route::get('/ads-iklan', [KampanyeController::class, 'adsIklan'])->name('ads_iklan');
            Route::get('/media-sosial', [KampanyeController::class, 'mediaSosial'])->name('media_sosial');
            Route::get('/website', [KampanyeController::class, 'website'])->name('website');
            Route::get('/youtube', [KampanyeController::class, 'youtube'])->name('youtube');
            Route::get('/event', [KampanyeController::class, 'event'])->name('event');
            Route::get('/buzzer', [KampanyeController::class, 'buzzer'])->name('buzzer');
        });
    Route::prefix('tools')
        ->name('tools.')
        ->group(function () {
            Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel');
            Route::get('/artikel/editorial', [ArtikelController::class, 'dashboardEditorial'])->name('artikel.editorial');
            Route::get('/artikel/editorial/pdf', [ArtikelController::class, 'exportDashboardPdf'])->name('artikel.editorial.pdf');
            Route::post('/artikel/editorial/preset', [ArtikelController::class, 'simpanPresetEditorial'])->name('artikel.editorial.preset.store');
            Route::delete('/artikel/editorial/preset/{presetEditorialPengguna}', [ArtikelController::class, 'hapusPresetEditorial'])->name('artikel.editorial.preset.destroy');
            Route::get('/artikel/saya', [ArtikelController::class, 'saya'])->name('artikel.saya');
            Route::get('/artikel/buat', [ArtikelController::class, 'create'])->name('artikel.create');
            Route::post('/artikel', [ArtikelController::class, 'store'])->name('artikel.store');
            Route::get('/artikel/kategori', [KategoriArtikelController::class, 'index'])->name('artikel.kategori.index');
            Route::post('/artikel/kategori', [KategoriArtikelController::class, 'store'])->name('artikel.kategori.store');
            Route::delete('/artikel/kategori/{kategoriArtikel}', [KategoriArtikelController::class, 'destroy'])->name('artikel.kategori.destroy');
            Route::get('/artikel/{artikel}/preview', [ArtikelController::class, 'preview'])->name('artikel.preview');
            Route::get('/artikel/{artikel}/pdf', [ArtikelController::class, 'exportPdf'])->name('artikel.pdf');
            Route::get('/artikel/{artikel}/ubah', [ArtikelController::class, 'edit'])->name('artikel.edit');
            Route::put('/artikel/{artikel}', [ArtikelController::class, 'update'])->name('artikel.update');
            Route::post('/artikel/{artikel}/autosimpan', [ArtikelController::class, 'autosimpan'])->name('artikel.autosimpan');
            Route::post('/artikel/{artikel}/revisi/{revisi}/pulihkan', [ArtikelController::class, 'pulihkanRevisi'])->name('artikel.revisi.pulihkan');
            Route::post('/artikel/{artikel}/terbitkan', [ArtikelController::class, 'terbitkan'])->name('artikel.terbitkan');
            Route::post('/artikel/{artikel}/batalkan-terbit', [ArtikelController::class, 'batalkanTerbit'])->name('artikel.batalkan_terbit');
            Route::delete('/artikel/{artikel}', [ArtikelController::class, 'destroy'])->name('artikel.destroy');
            Route::get('/artikel/{artikel}', [ArtikelController::class, 'show'])->name('artikel.show');
            Route::get('/link', [LinkController::class, 'index'])->name('link');
            Route::get('/link/analitik/export', LinkAnalitikExportController::class)->name('link.analitik.export');
            Route::post('/link', [LinkController::class, 'store'])->name('link.store');
            Route::put('/link/profil-publik', [LinkController::class, 'perbaruiProfilPublik'])->name('link.profil.update');
            Route::put('/link/{linkPengguna}', [LinkController::class, 'update'])->name('link.update');
            Route::delete('/link/{linkPengguna}', [LinkController::class, 'destroy'])->name('link.destroy');
        });

    Route::post('/keluar', [SesiController::class, 'destroy'])->name('logout');

    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
    Route::post('/leads/tindak-lanjut', [LeadController::class, 'storeTindakLanjut'])->name('leads.tindak-lanjut.store');

    Route::prefix('pengaturan/pengguna')
        ->name('pengaturan.pengguna.')
        ->middleware('level_akses:superadmin')
        ->group(function () {
            Route::get('/', [PenggunaController::class, 'index'])->name('index');
            Route::get('/tambah', [PenggunaController::class, 'create'])->name('create');
            Route::post('/', [PenggunaController::class, 'store'])->name('store');
            Route::get('/{pengguna}/ubah', [PenggunaController::class, 'edit'])->name('edit');
            Route::put('/{pengguna}', [PenggunaController::class, 'update'])->name('update');
            Route::delete('/{pengguna}', [PenggunaController::class, 'destroy'])->name('destroy');
        });

    Route::middleware('level_akses:superadmin')->group(function () {
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('/siswa/{siswa}/ubah', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('/produk/{produk}/ubah', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    });
});
