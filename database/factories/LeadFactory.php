<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'pic_id' => User::factory(),
            'nama_siswa' => fake()->name(),
            'asal_sekolah' => 'SMA '.fake()->city(),
            'nomor_telepon' => '08'.fake()->numerify('##########'),
            'channel' => fake()->randomElement(array_keys(Lead::channelOptions())),
            'sumber' => fake()->randomElement(['Ads/Iklan', 'Referensi', 'Organik']),
            'status' => fake()->randomElement(array_keys(Lead::statusOptions())),
            'jadwal_tindak_lanjut' => now()->addDays(fake()->numberBetween(1, 5)),
            'catatan' => fake()->sentence(),
            'kontak_terakhir' => now()->subDay(),
        ];
    }
}
