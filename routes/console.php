<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:bersihkan-data-contoh {--force : Jalankan pembersihan tanpa konfirmasi tambahan} {--hapus-akun-demo : Hapus juga akun demo yang terdaftar di konfigurasi data awal}', function () {
    if (! $this->option('force')) {
        $this->warn('Perintah ini akan menghapus data contoh/demo dari seluruh modul utama.');

        if (! $this->confirm('Lanjutkan pembersihan data contoh?')) {
            $this->comment('Pembersihan dibatalkan.');

            return self::SUCCESS;
        }
    }

    $tabelModul = [
        'sessions',
        'notifikasi_pengguna',
        'log_aktivitas',
        'histori_progres_tugas',
        'tugas_proyek',
        'proyek',
        'percobaan_kuis_pengguna',
        'hasil_kuis_pengguna',
        'progres_belajar_materi',
        'bank_soal_kuis_lms',
        'pertanyaan_kuis',
        'kuis_lms',
        'bank_soal_lms',
        'materi_kursus',
        'kursus',
        'aktivitas_link_publik',
        'statistik_link_harian',
        'link_pengguna',
        'preset_editorial_pengguna',
        'artikel_revisi',
        'artikel',
        'kategori_artikel',
        'lead_tindak_lanjut',
        'leads',
        'data_siswa',
        'produk_items',
    ];

    DB::transaction(function () use ($tabelModul): void {
        foreach ($tabelModul as $namaTabel) {
            DB::table($namaTabel)->delete();
        }
    });

    $jumlahAkunDemoDihapus = 0;

    if ($this->option('hapus-akun-demo')) {
        $emailAkunDemo = collect(config('data_awal.akun_demo', []))
            ->pluck('email')
            ->filter()
            ->values();

        if ($emailAkunDemo->isNotEmpty()) {
            $jumlahAkunDemoDihapus = DB::table('users')
                ->whereIn('email', $emailAkunDemo->all())
                ->delete();
        }
    }

    $this->info('Data contoh/demo modul utama berhasil dibersihkan.');

    if ($this->option('hapus-akun-demo')) {
        $this->line("Akun demo yang dihapus: {$jumlahAkunDemoDihapus}");
    }

    $this->line('Akun admin awal tetap dipertahankan sesuai konfigurasi data awal.');

    return self::SUCCESS;
})->purpose('Membersihkan data contoh/demo dari modul utama aplikasi');
