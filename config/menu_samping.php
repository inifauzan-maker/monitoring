<?php

return [
    [
        'bagian' => 'Utama',
        'items' => [
            [
                'judul' => 'Beranda',
                'ikon' => 'beranda',
                'route' => 'dashboard.beranda',
                'aktif' => ['dashboard.beranda'],
                'level_minimal' => 'level_5',
            ],
        ],
    ],
    [
        'bagian' => 'Menu Utama',
        'items' => [
            [
                'judul' => 'Omzet',
                'ikon' => 'omzet',
                'route' => 'omzet.index',
                'aktif' => ['omzet.*'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Siswa',
                'ikon' => 'siswa',
                'route' => 'siswa.index',
                'aktif' => ['siswa.*'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Leads',
                'ikon' => 'leads',
                'route' => 'leads.index',
                'aktif' => ['leads.*'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Produk',
                'ikon' => 'produk',
                'route' => 'produk.index',
                'aktif' => ['produk.*'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Kampanye',
                'ikon' => 'kampanye',
                'aktif' => ['kampanye.*'],
                'level_minimal' => 'level_5',
                'anak' => [
                    [
                        'judul' => 'Ads/Iklan',
                        'ikon' => 'ads_iklan',
                        'route' => 'kampanye.ads_iklan',
                        'aktif' => ['kampanye.ads_iklan'],
                        'level_minimal' => 'level_5',
                    ],
                    [
                        'judul' => 'Media Sosial',
                        'ikon' => 'media_sosial',
                        'route' => 'kampanye.media_sosial',
                        'aktif' => ['kampanye.media_sosial'],
                        'level_minimal' => 'level_5',
                    ],
                    [
                        'judul' => 'Website',
                        'ikon' => 'website',
                        'route' => 'kampanye.website',
                        'aktif' => ['kampanye.website'],
                        'level_minimal' => 'level_5',
                    ],
                    [
                        'judul' => 'Youtube',
                        'ikon' => 'youtube',
                        'route' => 'kampanye.youtube',
                        'aktif' => ['kampanye.youtube'],
                        'level_minimal' => 'level_5',
                    ],
                    [
                        'judul' => 'Event',
                        'ikon' => 'event',
                        'route' => 'kampanye.event',
                        'aktif' => ['kampanye.event'],
                        'level_minimal' => 'level_5',
                    ],
                    [
                        'judul' => 'Buzzer',
                        'ikon' => 'buzzer',
                        'route' => 'kampanye.buzzer',
                        'aktif' => ['kampanye.buzzer'],
                        'level_minimal' => 'level_5',
                    ],
                ],
            ],
        ],
    ],
    [
        'bagian' => 'LMS',
        'items' => [
            [
                'judul' => 'Kursus',
                'ikon' => 'kursus',
                'route' => 'lms.kursus',
                'aktif' => ['lms.kursus'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Materi',
                'ikon' => 'materi',
                'route' => 'lms.materi',
                'aktif' => ['lms.materi'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Playlist',
                'ikon' => 'playlist',
                'route' => 'lms.playlist',
                'aktif' => ['lms.playlist'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Progres Belajar',
                'ikon' => 'progres_belajar',
                'route' => 'lms.progres_belajar',
                'aktif' => ['lms.progres_belajar'],
                'level_minimal' => 'level_5',
            ],
        ],
    ],
    [
        'bagian' => 'Projects',
        'items' => [
            [
                'judul' => 'Daftar Project',
                'ikon' => 'daftar_project',
                'route' => 'proyek.daftar_project',
                'aktif' => ['proyek.daftar_project', 'proyek.edit', 'proyek.store', 'proyek.update', 'proyek.destroy'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Detail Tugas',
                'ikon' => 'detail_tugas',
                'route' => 'proyek.detail_tugas',
                'aktif' => ['proyek.detail_tugas', 'proyek.tugas.*'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Alur & SOP',
                'ikon' => 'alur_sop',
                'route' => 'proyek.alur_sop',
                'aktif' => ['proyek.alur_sop'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Penanggung Jawab',
                'ikon' => 'penanggung_jawab',
                'route' => 'proyek.penanggung_jawab',
                'aktif' => ['proyek.penanggung_jawab'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Progres',
                'ikon' => 'progres',
                'route' => 'proyek.progres',
                'aktif' => ['proyek.progres'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Evaluasi',
                'ikon' => 'evaluasi',
                'route' => 'proyek.evaluasi',
                'aktif' => ['proyek.evaluasi'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Pelaporan',
                'ikon' => 'pelaporan',
                'route' => 'proyek.pelaporan',
                'aktif' => ['proyek.pelaporan'],
                'level_minimal' => 'level_5',
            ],
        ],
    ],
    [
        'bagian' => 'Tools',
        'items' => [
            [
                'judul' => 'Artikel',
                'ikon' => 'artikel',
                'route' => 'tools.artikel',
                'aktif' => ['tools.artikel', 'tools.artikel.*'],
                'level_minimal' => 'level_5',
            ],
            [
                'judul' => 'Link',
                'ikon' => 'link',
                'route' => 'tools.link',
                'aktif' => ['tools.link'],
                'level_minimal' => 'level_5',
            ],
        ],
    ],
    [
        'bagian' => 'Administrasi',
        'items' => [
            [
                'judul' => 'Log Aktivitas',
                'ikon' => 'log_aktivitas',
                'route' => 'administrasi.log_aktivitas.index',
                'aktif' => ['administrasi.log_aktivitas.*'],
                'level_minimal' => 'superadmin',
            ],
            [
                'judul' => 'Pengguna (RBAC)',
                'ikon' => 'pengguna',
                'route' => 'pengaturan.pengguna.index',
                'aktif' => ['pengaturan.pengguna.*'],
                'level_minimal' => 'superadmin',
            ],
        ],
    ],
];
