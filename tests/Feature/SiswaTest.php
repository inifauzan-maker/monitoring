<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\DataSiswa;
use App\Models\ProdukItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_siswa(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('siswa.index'));

        $response
            ->assertOk()
            ->assertSee('Pusat data siswa');
    }

    public function test_non_superadmin_tidak_bisa_menambah_data_siswa(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();
        $produk = ProdukItem::factory()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('siswa.store'), [
                'produk_item_id' => $produk->id,
                'nama_lengkap' => 'Bima Aditya',
                'sistem_pembayaran' => 'lunas',
                'status_validasi' => 'pending',
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_data_siswa_dengan_invoice_otomatis(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $produk = ProdukItem::factory()->create([
            'program' => 'senirupa',
            'nama' => 'Kelas Intensif Senirupa',
            'biaya_daftar' => 100000,
            'biaya_pendidikan' => 900000,
            'discount' => 100000,
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('siswa.store'), [
                'produk_item_id' => $produk->id,
                'nama_lengkap' => 'Bima Aditya',
                'asal_sekolah' => 'SMA 5 Bandung',
                'sistem_pembayaran' => 'angsuran',
                'status_validasi' => 'validated',
                'jumlah_pembayaran' => 300000,
            ]);

        $response->assertRedirect(route('siswa.index'));

        $this->assertDatabaseHas('data_siswa', [
            'nama_lengkap' => 'Bima Aditya',
            'program' => 'senirupa',
            'nama_program' => 'Kelas Intensif Senirupa',
            'total_invoice' => 900000,
            'jumlah_pembayaran' => 300000,
            'status_pembayaran' => 'sebagian',
        ]);
    }

    public function test_superadmin_bisa_memfilter_siswa_berdasarkan_program(): void
    {
        $pengguna = User::factory()->superadmin()->create();

        $produkSenirupa = ProdukItem::factory()->create([
            'program' => 'senirupa',
            'nama' => 'Program Senirupa',
        ]);

        $produkArsitektur = ProdukItem::factory()->create([
            'program' => 'arsitektur',
            'nama' => 'Program Arsitektur',
        ]);

        DataSiswa::factory()->create([
            'produk_item_id' => $produkSenirupa->id,
            'program' => 'senirupa',
            'nama_program' => 'Program Senirupa',
            'nama_lengkap' => 'Siswa Senirupa',
        ]);

        DataSiswa::factory()->create([
            'produk_item_id' => $produkArsitektur->id,
            'program' => 'arsitektur',
            'nama_program' => 'Program Arsitektur',
            'nama_lengkap' => 'Siswa Arsitektur',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('siswa.index', ['program' => 'senirupa']));

        $response
            ->assertOk()
            ->assertSee('Siswa Senirupa')
            ->assertDontSee('Siswa Arsitektur');
    }
}
