<?php

namespace Tests\Feature;

use App\Enums\LevelAksesPengguna;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_bisa_melihat_halaman_leads(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->get(route('leads.index'));

        $response
            ->assertOk()
            ->assertSee('Pipeline prospek');
    }

    public function test_pengguna_bisa_menambah_lead(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_5)->create();

        $response = $this
            ->actingAs($pengguna)
            ->post(route('leads.store'), [
                'nama_siswa' => 'Ayu Putri',
                'asal_sekolah' => 'SMA 1 Bandung',
                'nomor_telepon' => '081234567890',
                'channel' => 'Instagram',
                'sumber' => 'Ads/Iklan',
                'status' => 'prospek',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'nama_siswa' => 'Ayu Putri',
            'channel' => 'Instagram',
            'status' => 'prospek',
        ]);

        $idLead = Lead::query()->where('nama_siswa', 'Ayu Putri')->value('id');

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'lead',
            'aksi' => 'tambah',
            'subjek_id' => $idLead,
        ]);
    }

    public function test_pengguna_bisa_mencatat_tindak_lanjut_lead(): void
    {
        $pengguna = User::factory()->denganLevelAkses(LevelAksesPengguna::LEVEL_4)->create();
        $lead = Lead::factory()->create([
            'created_by' => $pengguna->id,
            'pic_id' => $pengguna->id,
            'status' => 'prospek',
        ]);

        $response = $this
            ->actingAs($pengguna)
            ->post(route('leads.tindak-lanjut.store'), [
                'lead_id' => $lead->id,
                'catatan' => 'Sudah dihubungi via WhatsApp.',
                'status' => 'direncanakan',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('lead_tindak_lanjut', [
            'lead_id' => $lead->id,
            'status' => 'direncanakan',
        ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'follow_up',
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $pengguna->id,
            'modul' => 'lead',
            'aksi' => 'tindak_lanjut',
            'subjek_id' => $lead->id,
        ]);
    }
}
