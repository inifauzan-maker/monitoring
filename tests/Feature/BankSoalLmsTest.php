<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\BankSoalLms;
use App\Models\Kursus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankSoalLmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_bank_soal_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();
        $kursus = Kursus::factory()->create(['judul' => 'Kursus Bank Soal']);
        BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'pertanyaan' => 'Apa fungsi dashboard monitoring?',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.bank_soal'));

        $response
            ->assertOk()
            ->assertSee('Bank Soal')
            ->assertSee('Apa fungsi dashboard monitoring?')
            ->assertSee('Kursus Bank Soal');
    }

    public function test_pengguna_non_superadmin_tidak_bisa_menambah_bank_soal_lms(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_3)->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.bank_soal.store'), [
                'pertanyaan' => 'Soal terlarang?',
                'opsi_1' => 'A',
                'opsi_2' => 'B',
                'opsi_3' => 'C',
                'opsi_4' => 'D',
                'indeks_jawaban_benar' => 0,
                'tingkat_kesulitan' => 'mudah',
            ]);

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_bank_soal_lms(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create(['judul' => 'Kursus SOP']);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('lms.bank_soal.store'), [
                'kursus_id' => $kursus->id,
                'pertanyaan' => 'Apa standar dokumentasi SOP?',
                'opsi_1' => 'Asal ada',
                'opsi_2' => 'Jelas dan konsisten',
                'opsi_3' => 'Rahasia penuh',
                'opsi_4' => 'Tidak perlu',
                'indeks_jawaban_benar' => 1,
                'tingkat_kesulitan' => 'sulit',
                'aktif' => 1,
            ]);

        $response->assertRedirect(route('lms.bank_soal'));

        $this->assertDatabaseHas('bank_soal_lms', [
            'kursus_id' => $kursus->id,
            'pertanyaan' => 'Apa standar dokumentasi SOP?',
            'indeks_jawaban_benar' => 1,
            'tingkat_kesulitan' => 'sulit',
            'aktif' => true,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'bank_soal',
            'aksi' => 'tambah',
        ]);
    }

    public function test_bank_soal_lms_bisa_difilter_berdasarkan_tingkat_kesulitan(): void
    {
        $pengguna = User::factory()->superadmin()->create();
        $kursus = Kursus::factory()->create();

        BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'pertanyaan' => 'Soal mudah',
            'tingkat_kesulitan' => 'mudah',
        ]);

        BankSoalLms::factory()->create([
            'kursus_id' => $kursus->id,
            'pertanyaan' => 'Soal sulit',
            'tingkat_kesulitan' => 'sulit',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->get(route('lms.bank_soal', ['tingkat_kesulitan' => 'sulit']));

        $response
            ->assertOk()
            ->assertSee('Soal sulit')
            ->assertDontSee('Soal mudah');
    }
}
