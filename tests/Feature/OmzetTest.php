<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\DataSiswa;
use App\Models\ProdukItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OmzetTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_omzet(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('omzet.index'));

        $response
            ->assertOk()
            ->assertSee('Rekap tagihan');
    }

    public function test_halaman_omzet_menampilkan_rekap_invoice_dan_pembayaran(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $produk = ProdukItem::factory()->create([
            'program' => 'senirupa',
            'nama' => 'Kelas Reguler',
        ]);

        DataSiswa::factory()->create([
            'produk_item_id' => $produk->id,
            'program' => 'senirupa',
            'nama_program' => 'Kelas Reguler',
            'total_invoice' => 1000000,
            'jumlah_pembayaran' => 600000,
            'sisa_tagihan' => 400000,
            'status_validasi' => 'validated',
            'status_pembayaran' => 'sebagian',
        ]);

        DataSiswa::factory()->create([
            'produk_item_id' => $produk->id,
            'program' => 'senirupa',
            'nama_program' => 'Kelas Reguler',
            'total_invoice' => 500000,
            'jumlah_pembayaran' => 500000,
            'sisa_tagihan' => 0,
            'status_validasi' => 'validated',
            'status_pembayaran' => 'lunas',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('omzet.index', ['tahun' => now()->year]));

        $response
            ->assertOk()
            ->assertSee('Rp 1.500.000')
            ->assertSee('Rp 1.100.000')
            ->assertSee('Rp 400.000');
    }

    public function test_halaman_omzet_bisa_difilter_berdasarkan_status_pembayaran(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $produk = ProdukItem::factory()->create();

        DataSiswa::factory()->create([
            'produk_item_id' => $produk->id,
            'nama_lengkap' => 'Siswa Lunas',
            'status_validasi' => 'validated',
            'status_pembayaran' => 'lunas',
            'sisa_tagihan' => 0,
        ]);

        DataSiswa::factory()->create([
            'produk_item_id' => $produk->id,
            'nama_lengkap' => 'Siswa Belum Lunas',
            'status_validasi' => 'validated',
            'status_pembayaran' => 'sebagian',
            'sisa_tagihan' => 100000,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('omzet.index', [
                'tahun' => now()->year,
                'status_pembayaran' => 'lunas',
            ]));

        $response
            ->assertOk()
            ->assertSee('Siswa Lunas')
            ->assertDontSee('Siswa Belum Lunas');
    }
}
