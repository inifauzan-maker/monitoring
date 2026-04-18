<?php

namespace Database\Factories;

use App\Models\KategoriArtikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KategoriArtikel>
 */
class KategoriArtikelFactory extends Factory
{
    protected $model = KategoriArtikel::class;

    public function definition(): array
    {
        $nama = fake()->unique()->words(2, true);

        return [
            'nama' => Str::title($nama),
            'slug' => Str::slug($nama),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
