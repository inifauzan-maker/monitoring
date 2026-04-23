<?php

namespace Database\Factories;

use App\Models\KuisLms;
use App\Models\PercobaanKuisPengguna;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PercobaanKuisPengguna>
 */
class PercobaanKuisPenggunaFactory extends Factory
{
    protected $model = PercobaanKuisPengguna::class;

    public function definition(): array
    {
        $jumlahPertanyaan = fake()->numberBetween(3, 10);
        $jumlahBenar = fake()->numberBetween(0, $jumlahPertanyaan);
        $skor = (int) round(($jumlahBenar / $jumlahPertanyaan) * 100);

        return [
            'kuis_lms_id' => KuisLms::factory(),
            'user_id' => User::factory(),
            'percobaan_ke' => 1,
            'paket_soal' => [],
            'jawaban_pengguna' => [],
            'jumlah_benar' => $jumlahBenar,
            'jumlah_pertanyaan' => $jumlahPertanyaan,
            'skor' => $skor,
            'lulus' => $skor >= 70,
            'selesai_pada' => now(),
        ];
    }
}
