<?php

namespace Database\Factories;

use App\Models\KuisLms;
use App\Models\PertanyaanKuis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PertanyaanKuis>
 */
class PertanyaanKuisFactory extends Factory
{
    protected $model = PertanyaanKuis::class;

    public function definition(): array
    {
        return [
            'kuis_lms_id' => KuisLms::factory(),
            'pertanyaan' => fake()->sentence(8).'? ',
            'opsi_jawaban' => [
                'Opsi A',
                'Opsi B',
                'Opsi C',
                'Opsi D',
            ],
            'indeks_jawaban_benar' => 1,
            'urutan' => fake()->numberBetween(1, 10),
        ];
    }
}
