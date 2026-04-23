<?php

namespace Database\Factories;

use App\Models\HasilKuisPengguna;
use App\Models\KuisLms;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HasilKuisPengguna>
 */
class HasilKuisPenggunaFactory extends Factory
{
    protected $model = HasilKuisPengguna::class;

    public function definition(): array
    {
        $jumlahPertanyaan = fake()->numberBetween(3, 10);
        $jumlahBenar = fake()->numberBetween(0, $jumlahPertanyaan);
        $skor = (int) round(($jumlahBenar / $jumlahPertanyaan) * 100);

        return [
            'kuis_lms_id' => KuisLms::factory(),
            'user_id' => User::factory(),
            'jawaban_pengguna' => [],
            'jumlah_benar' => $jumlahBenar,
            'jumlah_pertanyaan' => $jumlahPertanyaan,
            'skor' => $skor,
            'lulus' => $skor >= 70,
            'jumlah_percobaan' => 1,
            'selesai_pada' => now(),
        ];
    }
}
