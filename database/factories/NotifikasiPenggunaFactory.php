<?php

namespace Database\Factories;

use App\Models\NotifikasiPengguna;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotifikasiPengguna>
 */
class NotifikasiPenggunaFactory extends Factory
{
    protected $model = NotifikasiPengguna::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => fake()->sentence(4),
            'pesan' => fake()->sentence(10),
            'tipe' => fake()->randomElement(array_keys(NotifikasiPengguna::opsiTipe())),
            'tautan' => null,
            'metadata' => null,
            'dibaca_pada' => null,
        ];
    }

    public function sudahDibaca(): static
    {
        return $this->state(fn () => [
            'dibaca_pada' => now(),
        ]);
    }
}
