<?php

namespace Database\Factories;

use App\Models\BankSoalLms;
use App\Models\Kursus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankSoalLms>
 */
class BankSoalLmsFactory extends Factory
{
    protected $model = BankSoalLms::class;

    public function definition(): array
    {
        return [
            'kursus_id' => Kursus::factory(),
            'pertanyaan' => fake()->sentence(9).'? ',
            'opsi_jawaban' => [
                'Opsi A',
                'Opsi B',
                'Opsi C',
                'Opsi D',
            ],
            'indeks_jawaban_benar' => fake()->numberBetween(0, 3),
            'tingkat_kesulitan' => fake()->randomElement(['mudah', 'menengah', 'sulit']),
            'aktif' => true,
        ];
    }
}
