<?php

namespace Database\Factories;

use App\Models\Kursus;
use App\Models\MateriKursus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MateriKursus>
 */
class MateriKursusFactory extends Factory
{
    protected $model = MateriKursus::class;

    public function definition(): array
    {
        return [
            'kursus_id' => Kursus::factory(),
            'judul' => fake()->sentence(4),
            'youtube_id' => fake()->lexify('video???????'),
            'durasi_detik' => fake()->numberBetween(120, 3600),
            'urutan' => fake()->numberBetween(1, 20),
        ];
    }
}
