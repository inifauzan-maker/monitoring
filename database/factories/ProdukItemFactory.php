<?php

namespace Database\Factories;

use App\Models\ProdukItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdukItem>
 */
class ProdukItemFactory extends Factory
{
    protected $model = ProdukItem::class;

    public function definition(): array
    {
        $biayaDaftar = fake()->numberBetween(100000, 500000);
        $biayaPendidikan = fake()->numberBetween(1000000, 5000000);
        $discount = fake()->numberBetween(0, 300000);
        $siswa = fake()->numberBetween(1, 25);

        return [
            'program' => fake()->randomElement(array_keys(ProdukItem::programOptions())),
            'tahun_ajaran' => '2026 - 2027',
            'kode_1' => strtoupper(fake()->bothify('??##')),
            'kode_2' => strtoupper(fake()->bothify('??##')),
            'kode_3' => strtoupper(fake()->bothify('??##')),
            'kode_4' => strtoupper(fake()->bothify('??##')),
            'nama' => 'Program '.fake()->randomElement(['Reguler', 'Intensif', 'Privat']),
            'biaya_daftar' => $biayaDaftar,
            'biaya_pendidikan' => $biayaPendidikan,
            'discount' => $discount,
            'siswa' => $siswa,
            'omzet' => max(0, $biayaDaftar + $biayaPendidikan - $discount) * $siswa,
        ];
    }
}
