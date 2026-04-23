<?php

namespace Database\Factories;

use App\Models\Kursus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kursus>
 */
class KursusFactory extends Factory
{
    protected $model = Kursus::class;

    public function definition(): array
    {
        $judul = fake()->unique()->sentence(3);

        return [
            'judul' => Str::title($judul),
            'slug' => Str::slug($judul),
            'ringkasan' => fake()->sentence(12),
            'thumbnail_url' => fake()->imageUrl(640, 360, 'education', true, 'kursus'),
        ];
    }
}
