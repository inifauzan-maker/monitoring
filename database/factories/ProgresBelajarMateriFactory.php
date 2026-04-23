<?php

namespace Database\Factories;

use App\Models\MateriKursus;
use App\Models\ProgresBelajarMateri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgresBelajarMateri>
 */
class ProgresBelajarMateriFactory extends Factory
{
    protected $model = ProgresBelajarMateri::class;

    public function definition(): array
    {
        $persenProgres = fake()->numberBetween(0, 100);
        $selesai = $persenProgres === 100 && fake()->boolean(70);

        return [
            'user_id' => User::factory(),
            'materi_kursus_id' => MateriKursus::factory(),
            'detik_terakhir' => fake()->numberBetween(0, 1800),
            'persen_progres' => $persenProgres,
            'selesai_pada' => $selesai ? now() : null,
        ];
    }
}
