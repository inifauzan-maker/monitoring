<?php

namespace Database\Factories;

use App\Models\Proyek;
use App\Models\TugasProyek;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TugasProyek>
 */
class TugasProyekFactory extends Factory
{
    protected $model = TugasProyek::class;

    public function definition(): array
    {
        $persentase = fake()->numberBetween(0, 100);
        $status = $persentase >= 100
            ? 'selesai'
            : fake()->randomElement(['belum_mulai', 'berjalan', 'review', 'tertunda']);

        return [
            'proyek_id' => Proyek::factory(),
            'judul_tugas' => fake()->sentence(4),
            'deskripsi_tugas' => fake()->sentence(),
            'status_tugas' => $status,
            'prioritas_tugas' => fake()->randomElement(array_keys(TugasProyek::prioritasOptions())),
            'persentase_progres' => $status === 'selesai' ? 100 : $persentase,
            'penanggung_jawab_id' => User::factory(),
            'tanggal_mulai' => fake()->dateTimeBetween('-20 days', '+2 days'),
            'tanggal_target' => fake()->dateTimeBetween('+1 days', '+30 days'),
            'tanggal_selesai' => $status === 'selesai' ? fake()->dateTimeBetween('-5 days', 'now') : null,
            'catatan_tugas' => fake()->sentence(),
            'urutan' => fake()->numberBetween(0, 20),
        ];
    }
}
