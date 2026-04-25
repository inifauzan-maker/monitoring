<?php

return [
    'admin' => [
        'name' => env('ADMIN_AWAL_NAMA', 'Admin Simarketing'),
        'email' => env('ADMIN_AWAL_EMAIL', 'admin@simarketing.local'),
        'password' => env('ADMIN_AWAL_PASSWORD', 'password'),
        'level_akses' => env('ADMIN_AWAL_LEVEL_AKSES', 'superadmin'),
    ],
    'seed_data_contoh' => (bool) filter_var(env('SEED_DATA_CONTOH', false), FILTER_VALIDATE_BOOL),
    'password_akun_demo' => env('PASSWORD_AKUN_DEMO', 'password'),
    'akun_demo' => [
        [
            'name' => 'Pengguna Level 1',
            'email' => 'level1@example.test',
            'level_akses' => 'level_1',
        ],
        [
            'name' => 'Pengguna Level 2',
            'email' => 'level2@example.test',
            'level_akses' => 'level_2',
        ],
        [
            'name' => 'Pengguna Level 3',
            'email' => 'level3@example.test',
            'level_akses' => 'level_3',
        ],
        [
            'name' => 'Pengguna Level 4',
            'email' => 'level4@example.test',
            'level_akses' => 'level_4',
        ],
        [
            'name' => 'Pengguna Level 5',
            'email' => 'level5@example.test',
            'level_akses' => 'level_5',
        ],
    ],
];
