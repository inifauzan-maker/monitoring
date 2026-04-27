<?php

use App\Models\User;
use App\Support\AvatarLinkPublikStorage;
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

Artisan::command('app:rapikan-avatar-link {--force : Jalankan perapian tanpa konfirmasi tambahan} {--hapus-legacy : Hapus file avatar lama setelah berhasil dipindahkan}', function () {
    if (! $this->option('force')) {
        $this->warn('Perintah ini akan merapikan path avatar publik dan memindahkan file lama ke disk avatar baru jika diperlukan.');

        if (! $this->confirm('Lanjutkan perapian avatar link publik?')) {
            $this->comment('Perapian avatar dibatalkan.');

            return self::SUCCESS;
        }
    }

    $diproses = 0;
    $dirapikan = 0;
    $dipindahkan = 0;
    $hilang = 0;
    $hapusLegacy = (bool) $this->option('hapus-legacy');
    $diskAktif = AvatarLinkPublikStorage::disk();
    $diskLegacy = AvatarLinkPublikStorage::legacyDisk();

    User::query()
        ->whereNotNull('avatar_link')
        ->select(['id', 'email', 'avatar_link'])
        ->orderBy('id')
        ->chunkById(100, function ($penggunaBatch) use (&$diproses, &$dirapikan, &$dipindahkan, &$hilang, $hapusLegacy, $diskAktif, $diskLegacy): void {
            foreach ($penggunaBatch as $pengguna) {
                $diproses++;
                $pathAwal = $pengguna->avatar_link;
                $pathNormal = AvatarLinkPublikStorage::normalisasiPath($pathAwal);

                if (! $pathNormal) {
                    $hilang++;
                    continue;
                }

                $sudahAdaDiDiskAktif = \Illuminate\Support\Facades\Storage::disk($diskAktif)->exists($pathNormal);
                $adaDiDiskLegacy = $diskLegacy !== $diskAktif
                    && \Illuminate\Support\Facades\Storage::disk($diskLegacy)->exists($pathNormal);
                $pathMigrasi = AvatarLinkPublikStorage::migrasikanJikaPerlu(
                    $pathNormal,
                    $hapusLegacy,
                );

                if ($pathMigrasi && $pathMigrasi === $pathNormal && $pathAwal !== $pathNormal) {
                    $pengguna->forceFill([
                        'avatar_link' => $pathNormal,
                    ])->save();

                    $dirapikan++;
                    continue;
                }

                if ($pathMigrasi) {
                    if ($pathAwal !== $pathMigrasi) {
                        $pengguna->forceFill([
                            'avatar_link' => $pathMigrasi,
                        ])->save();

                        $dirapikan++;
                    }

                    if (! $sudahAdaDiDiskAktif && $adaDiDiskLegacy) {
                        $dipindahkan++;
                    }

                    continue;
                }

                $hilang++;
            }
        });

    $this->info('Perapian avatar link publik selesai.');
    $this->line("Diproses: {$diproses}");
    $this->line("Path dirapikan: {$dirapikan}");
    $this->line("File dipindahkan: {$dipindahkan}");
    $this->line("File tidak ditemukan: {$hilang}");
    $this->line('Disk avatar aktif: '.AvatarLinkPublikStorage::disk());
    $this->line('Direktori avatar: '.AvatarLinkPublikStorage::directory());

    return self::SUCCESS;
})->purpose('Merapikan storage avatar publik agar konsisten di semua environment');
