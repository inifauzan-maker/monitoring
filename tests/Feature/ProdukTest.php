<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\ProdukItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdukTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_biasa_bisa_melihat_halaman_produk(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('produk.index'));

        $response
            ->assertOk()
            ->assertSee('Kelola data produk bimbel');
    }

    public function test_pengguna_non_superadmin_tidak_bisa_menambah_produk(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('produk.store'), [
                'program' => 'senirupa',
                'tahun_ajaran' => '2026 - 2027',
                'kode_1' => 'A1',
                'kode_2' => 'B1',
                'kode_3' => 'C1',
                'kode_4' => 'D1',
                'nama' => 'Program Reguler',
                'biaya_daftar' => 100000,
                'biaya_pendidikan' => 1000000,
                'discount' => 100000,
                'siswa' => 10,
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_produk_dan_omzet_dihitung_otomatis(): void
    {
        $pengguna = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('produk.store'), [
                'program' => 'senirupa',
                'tahun_ajaran' => '2026 - 2027',
                'kode_1' => 'A1',
                'kode_2' => 'B1',
                'kode_3' => 'C1',
                'kode_4' => 'D1',
                'nama' => 'Program Reguler',
                'biaya_daftar' => 100000,
                'biaya_pendidikan' => 1000000,
                'discount' => 100000,
                'siswa' => 10,
            ]);

        $response->assertRedirect(route('produk.index'));

        $this->assertDatabaseHas('produk_items', [
            'program' => 'senirupa',
            'nama' => 'Program Reguler',
            'omzet' => 10000000,
        ]);

        $idProduk = ProdukItem::query()->where('nama', 'Program Reguler')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'produk',
            'aksi' => 'tambah',
            'subjek_id' => $idProduk,
        ]);
    }

    public function test_superadmin_bisa_memfilter_produk_berdasarkan_program(): void
    {
        $pengguna = User::factory()->superadmin()->create();

        ProdukItem::factory()->create([
            'program' => 'senirupa',
            'nama' => 'Program Senirupa',
        ]);

        ProdukItem::factory()->create([
            'program' => 'arsitektur',
            'nama' => 'Program Arsitektur',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('produk.index', ['program' => 'senirupa']));

        $response
            ->assertOk()
            ->assertSee('Program Senirupa')
            ->assertDontSee('Program Arsitektur');
    }
}
