<?php

namespace App\Http\Controllers\Autentikasi;

use App\Http\Controllers\Controller;
use App\Support\PencatatLogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SesiController extends Controller
{
    public function create(): View
    {
        return view('autentikasi.masuk');
    }

    public function store(Request $request): RedirectResponse
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'email',
            'password' => 'password',
        ]);

        if (! Auth::attempt($kredensial, $request->boolean('ingat_saya'))) {
            PencatatLogAktivitas::catat(
                $request,
                'autentikasi',
                'masuk_gagal',
                'Percobaan masuk gagal.',
                'sesi',
                ['email' => $kredensial['email']],
                null,
            );

            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        PencatatLogAktivitas::catat(
            $request,
            'autentikasi',
            'masuk',
            'Pengguna berhasil masuk ke aplikasi.',
            $request->user(),
        );

        return redirect()->intended(route('dashboard.beranda'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $pengguna = $request->user();

        if ($pengguna) {
            PencatatLogAktivitas::catat(
                $request,
                'autentikasi',
                'keluar',
                'Pengguna keluar dari aplikasi.',
                $pengguna,
                [],
                $pengguna,
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
