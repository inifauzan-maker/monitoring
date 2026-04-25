<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AkunAwalSeeder::class);

        if (config('data_awal.seed_data_contoh', false)) {
            $this->call([
                AkunDemoSeeder::class,
                DataContohSeeder::class,
            ]);
        }
    }
}
