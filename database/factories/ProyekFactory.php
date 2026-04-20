<?php

namespace Database\Factories;

use App\Models\Proyek;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proyek>
 */
class ProyekFactory extends Factory
{
    protected $model = Proyek::class;

    public function definition(): array
    {
        $tanggalMulai = fake()->dateTimeBetween('-30 days', '+3 days');
        $tanggalTarget = fake()->dateTimeBetween($tanggalMulai, '+60 days');

        return [
            'kode_project' => 'PRJ-'.fake()->unique()->numerify('###'),
            'nama_project' => fake()->sentence(3),
            'klien' => fake()->company(),
            'status_project' => fake()->randomElement(array_keys(Proyek::statusOptions())),
            'prioritas_project' => fake()->randomElement(array_keys(Proyek::prioritasOptions())),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_target_selesai' => $tanggalTarget,
            'tanggal_selesai' => null,
            'deskripsi_project' => fake()->paragraph(),
            'alur_kerja' => fake()->sentence(8),
            'sop_ringkas' => fake()->sentence(10),
            'penanggung_jawab_id' => User::factory(),
            'skor_evaluasi' => fake()->numberBetween(60, 95),
            'catatan_evaluasi' => fake()->sentence(),
            'dibuat_oleh' => User::factory(),
        ];
    }
}
