<?php

$hostAplikasi = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';

return [
    'skema_domain_kustom' => env('LINK_PUBLIK_CUSTOM_DOMAIN_SCHEME', 'https'),
    'target_domain_kustom' => env('LINK_PUBLIK_CUSTOM_DOMAIN_TARGET', $hostAplikasi),
    'host_aplikasi' => array_values(array_filter(array_unique([
        strtolower($hostAplikasi),
        'localhost',
        '127.0.0.1',
    ]))),
];
