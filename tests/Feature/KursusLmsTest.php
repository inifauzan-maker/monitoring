<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Kursus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KursusLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_kursus_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        Kursus::factory()->create([
            'judul' => 'Kursus Laravel Dasar',
            'slug' => 'kursus-laravel-dasar',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kursus'));

        $response
            ->assertOk()
            ->assertSee('Kelola katalog kursus LMS')
            ->assertSee('Kursus Laravel Dasar');
    }

    public function test_pengguna_non_superadmin_tidak_bisa_menambah_kursus_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kursus.store'), [
                'judul' => 'Kursus Monitoring',
                'ringkasan' => 'Belajar monitoring dari dasar.',
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_kursus_lms_dengan_slug_otomatis(): void
    {
        $pengguna = User::factory()->superadmin()->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.kursus.store'), [
                'judul' => 'Kursus Monitoring Dasar',
                'ringkasan' => 'Belajar monitoring dari dasar.',
                'thumbnail_url' => 'https://example.com/thumbnail-monitoring.jpg',
            ]);

        $response->assertRedirect(route('lms.kursus'));

        $this->assertDatabaseHas('kursus', [
            'judul' => 'Kursus Monitoring Dasar',
            'slug' => 'kursus-monitoring-dasar',
        ]);

        $idKursus = Kursus::query()->where('slug', 'kursus-monitoring-dasar')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'kursus',
            'aksi' => 'tambah',
            'subjek_id' => $idKursus,
        ]);
    }

    public function test_pengguna_bisa_memfilter_kursus_lms_berdasarkan_kata_kunci(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        Kursus::factory()->create([
            'judul' => 'Kursus Laravel Dasar',
            'slug' => 'kursus-laravel-dasar',
        ]);

        Kursus::factory()->create([
            'judul' => 'Kursus Desain UI',
            'slug' => 'kursus-desain-ui',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.kursus', ['q' => 'laravel']));

        $response
            ->assertOk()
            ->assertSee('Kursus Laravel Dasar')
            ->assertDontSee('Kursus Desain UI');
    }
}
