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
                'judul' => 'Pengguna (RBAC)',
                'ikon' => 'pengguna',
                'route' => 'pengaturan.pengguna.index',
                'aktif' => ['pengaturan.pengguna.*'],
                'level_minimal' => 'superadmin',
            ],
        ],
    ],
];
