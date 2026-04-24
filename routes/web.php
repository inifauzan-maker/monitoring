<?php

use App\Http\Controllers\Autentikasi\SesiController;
use App\Http\Controllers\Administrasi\LogAktivitasController;
use App\Http\Controllers\Administrasi\PemetaanBerandaController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\KampanyeController;
use App\Http\Controllers\KategoriArtikelController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LinkAnalitikExportController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LinkPublikController;
use App\Http\Controllers\Lms\BankSoalController;
use App\Http\Controllers\Lms\KursusController;
use App\Http\Controllers\Lms\KuisController;
use App\Http\Controllers\Lms\MateriController;
use App\Http\Controllers\Lms\PlaylistController;
use App\Http\Controllers\Lms\ProgresBelajarController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\OmzetController;
use App\Http\Controllers\Pengaturan\PenggunaController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProyekController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\StatusAplikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [SesiController::class, 'create'])->name('login');
    Route::post('/masuk', [SesiController::class, 'store'])->name('sesi.store');
});

Route::get('/health', [StatusAplikasiController::class, 'show'])->name('health');
Route::get('/', [LinkPublikController::class, 'root'])->name('dashboard.beranda');
Route::get('/cta', [LinkPublikController::class, 'domainCta'])->name('publik.domain_link.cta');
Route::get('/link/{linkPengguna}', [LinkPublikController::class, 'domainBuka'])->name('publik.domain_link.buka');
Route::get('/u/{pengguna:slug_link}', [LinkPublikController::class, 'show'])->name('publik.link.show');
Route::get('/u/{pengguna:slug_link}/cta', [LinkPublikController::class, 'cta'])->name('publik.link.cta');
Route::get('/u/{pengguna:slug_link}/link/{linkPengguna}', [LinkPublikController::class, 'buka'])->name('publik.link.buka');

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca_semua');
    Route::patch('/notifikasi/{notifikasiPengguna}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::get('/omzet', [OmzetController::class, 'index'])->name('omzet.index');
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::prefix('lms')
        ->name('lms.')
        ->group(function () {
            Route::get('/kursus', [KursusController::class, 'index'])->name('kursus');
            Route::get('/bank-soal', [BankSoalController::class, 'index'])->name('bank_soal');
            Route::get('/kuis', [KuisController::class, 'index'])->name('kuis');
            Route::get('/kuis/{kuisLms}', [KuisController::class, 'show'])->name('kuis.show');
            Route::post('/kuis/{kuisLms}/submit', [KuisController::class, 'submit'])->name('kuis.submit');
            Route::post('/kuis/{kuisLms}/mulai-ulang', [KuisController::class, 'restart'])->name('kuis.restart');
            Route::get('/materi', [MateriController::class, 'index'])->name('materi');
            Route::get('/materi/{materiKursus}', [MateriController::class, 'show'])->name('materi.show');
            Route::post('/materi/{materiKursus}/progres', [ProgresBelajarController::class, 'sinkronOtomatis'])->name('materi.progres');
            Route::get('/playlist', [PlaylistController::class, 'index'])->name('playlist');
            Route::get('/progres-belajar', [ProgresBelajarController::class, 'index'])->name('progres_belajar');
            Route::post('/progres-belajar', [ProgresBelajarController::class, 'store'])->name('progres_belajar.store');
            Route::middleware('level_akses:superadmin')->group(function () {
                Route::post('/kursus', [KursusController::class, 'store'])->name('kursus.store');
                Route::get('/kursus/{kursus}/ubah', [KursusController::class, 'edit'])->name('kursus.edit');
                Route::put('/kursus/{kursus}', [KursusController::class, 'update'])->name('kursus.update');
                Route::delete('/kursus/{kursus}', [KursusController::class, 'destroy'])->name('kursus.destroy');
                Route::post('/bank-soal', [BankSoalController::class, 'store'])->name('bank_soal.store');
                Route::get('/bank-soal/{bankSoalLms}/ubah', [BankSoalController::class, 'edit'])->name('bank_soal.edit');
                Route::put('/bank-soal/{bankSoalLms}', [BankSoalController::class, 'update'])->name('bank_soal.update');
                Route::delete('/bank-soal/{bankSoalLms}', [BankSoalController::class, 'destroy'])->name('bank_soal.destroy');
                Route::post('/kuis', [KuisController::class, 'store'])->name('kuis.store');
                Route::get('/kuis/{kuisLms}/ubah', [KuisController::class, 'edit'])->name('kuis.edit');
                Route::put('/kuis/{kuisLms}', [KuisController::class, 'update'])->name('kuis.update');
                Route::delete('/kuis/{kuisLms}', [KuisController::class, 'destroy'])->name('kuis.destroy');
                Route::post('/kuis/{kuisLms}/bank-soal', [KuisController::class, 'storeBankSoal'])->name('kuis.bank_soal.store');
                Route::delete('/kuis/{kuisLms}/bank-soal/{bankSoalLms}', [KuisController::class, 'destroyBankSoal'])->name('kuis.bank_soal.destroy');
                Route::post('/kuis/{kuisLms}/pertanyaan', [KuisController::class, 'storePertanyaan'])->name('kuis.pertanyaan.store');
                Route::put('/kuis/{kuisLms}/pertanyaan/{pertanyaanKuis}', [KuisController::class, 'updatePertanyaan'])->name('kuis.pertanyaan.update');
                Route::delete('/kuis/{kuisLms}/pertanyaan/{pertanyaanKuis}', [KuisController::class, 'destroyPertanyaan'])->name('kuis.pertanyaan.destroy');
                Route::post('/materi', [MateriController::class, 'store'])->name('materi.store');
                Route::get('/materi/{materiKursus}/ubah', [MateriController::class, 'edit'])->name('materi.edit');
                Route::put('/materi/{materiKursus}', [MateriController::class, 'update'])->name('materi.update');
                Route::delete('/materi/{materiKursus}', [MateriController::class, 'destroy'])->name('materi.destroy');
                Route::post('/playlist', [PlaylistController::class, 'store'])->name('playlist.store');
            });
        });
    Route::prefix('proyek')
        ->name('proyek.')
        ->group(function () {
            Route::get('/daftar-project', [ProyekController::class, 'daftarProject'])->name('daftar_project');
            Route::post('/daftar-project', [ProyekController::class, 'store'])->name('store');
            Route::get('/daftar-project/{proyek}/ubah', [ProyekController::class, 'edit'])->name('edit');
            Route::put('/daftar-project/{proyek}', [ProyekController::class, 'update'])->name('update');
            Route::delete('/daftar-project/{proyek}', [ProyekController::class, 'destroy'])->name('destroy');
            Route::get('/detail-tugas', [ProyekController::class, 'detailTugas'])->name('detail_tugas');
            Route::post('/detail-tugas', [ProyekController::class, 'storeTugas'])->name('tugas.store');
            Route::get('/detail-tugas/{tugasProyek}/histori', [ProyekController::class, 'historiTugas'])->name('tugas.histori');
            Route::get('/detail-tugas/{tugasProyek}/ubah', [ProyekController::class, 'editTugas'])->name('tugas.edit');
            Route::patch('/detail-tugas/{tugasProyek}/status-cepat', [ProyekController::class, 'perbaruiStatusTugasCepat'])->name('tugas.status_cepat');
            Route::put('/detail-tugas/{tugasProyek}', [ProyekController::class, 'updateTugas'])->name('tugas.update');
            Route::delete('/detail-tugas/{tugasProyek}', [ProyekController::class, 'destroyTugas'])->name('tugas.destroy');
            Route::get('/alur-sop', [ProyekController::class, 'alurSop'])->name('alur_sop');
            Route::get('/penanggung-jawab', [ProyekController::class, 'penanggungJawab'])->name('penanggung_jawab');
            Route::get('/progres', [ProyekController::class, 'progres'])->name('progres');
            Route::get('/evaluasi', [ProyekController::class, 'evaluasi'])->name('evaluasi');
            Route::get('/pelaporan', [ProyekController::class, 'pelaporan'])->name('pelaporan');
        });
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
        Route::prefix('administrasi/pemetaan-beranda')
            ->name('administrasi.pemetaan_beranda.')
            ->group(function () {
                Route::get('/', [PemetaanBerandaController::class, 'index'])->name('index');
                Route::put('/', [PemetaanBerandaController::class, 'update'])->name('update');
            });

        Route::prefix('administrasi/log-aktivitas')
            ->name('administrasi.log_aktivitas.')
            ->group(function () {
                Route::get('/', [LogAktivitasController::class, 'index'])->name('index');
            });

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
