<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanPenggunaTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_bisa_membuka_pengaturan_pengguna(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($superadmin)
            ->get(route('pengaturan.pengguna.index'));

        $response
            ->assertOk()
            ->assertSee('Pengaturan Pengguna');
    }

    public function test_non_superadmin_tidak_bisa_membuka_pengaturan_pengguna(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('pengaturan.pengguna.index'));

        $response->assertForbidden();
    }
}
