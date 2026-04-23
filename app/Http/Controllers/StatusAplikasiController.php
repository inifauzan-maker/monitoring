<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusAplikasiController extends Controller
{
    public function show(): JsonResponse
    {
        $databaseSehat = false;

        try {
            DB::connection()->getPdo();
            $databaseSehat = true;
        } catch (\Throwable) {
            $databaseSehat = false;
        }

        $storageSehat = is_writable(storage_path()) && is_writable(base_path('bootstrap/cache'));
        $statusAplikasi = $databaseSehat && $storageSehat ? 'ok' : 'degradasi';

        return response()->json([
            'status' => $statusAplikasi,
            'aplikasi' => config('app.name', 'Monitoring'),
            'waktu_server' => now()->toIso8601String(),
            'dependensi' => [
                'database' => $databaseSehat ? 'ok' : 'gagal',
                'storage' => $storageSehat ? 'ok' : 'gagal',
            ],
        ], $statusAplikasi === 'ok' ? 200 : 503);
    }
}
