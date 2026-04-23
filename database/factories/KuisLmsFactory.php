<?php

namespace Database\Factories;

use App\Models\KuisLms;
use App\Models\Kursus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KuisLms>
 */
class KuisLmsFactory extends Factory
{
    protected $model = KuisLms::class;

    public function definition(): array
    {
        return [
            'judul' => 'Kuis '.fake()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'target_tipe' => 'kursus',
            'kursus_id' => Kursus::factory(),
            'materi_kursus_id' => null,
            'nilai_lulus' => 70,
            'durasi_menit' => null,
            'maksimal_percobaan' => null,
            'gunakan_bank_soal' => false,
            'acak_soal' => false,
            'acak_opsi_jawaban' => false,
            'jumlah_soal_tampil' => null,
            'aktif' => true,
        ];
    }
}
