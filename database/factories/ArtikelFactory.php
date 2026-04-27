<?php

namespace Database\Factories;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artikel>
 */
class ArtikelFactory extends Factory
{
    protected $model = Artikel::class;

    public function definition(): array
    {
        $judul = Str::title(fake()->unique()->words(5, true));

        return [
            'judul' => $judul,
            'slug' => Str::slug($judul),
            'kata_kunci_utama' => fake()->words(3, true),
            'keyword_turunan' => implode(",\n", fake()->words(4)),
            'ringkasan' => fake()->paragraph(),
            'konten' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'kategori_artikel_id' => KategoriArtikel::factory(),
            'penulis_id' => User::factory(),
            'tingkat_keahlian' => fake()->randomElement(array_keys(Artikel::TINGKAT_KEAHLIAN)),
            'bio_penulis' => fake()->sentence(),
            'sumber_referensi' => [fake()->url(), fake()->url()],
            'judul_seo' => Str::limit($judul, 60, ''),
            'deskripsi_seo' => Str::limit(fake()->sentence(18), 160, ''),
            'outline_seo' => "# {$judul}\n\n## Ringkasan\n## Pembahasan utama\n## Kesimpulan",
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => true,
                'metadata_seo_final' => true,
                'referensi_sudah_valid' => true,
                'konten_sudah_dicek' => true,
                'gambar_unggulan_siap' => true,
            ],
            'gambar_unggulan_path' => null,
            'alt_gambar_unggulan' => null,
            'sudah_diterbitkan' => true,
            'diterbitkan_pada' => now()->subDays(fake()->numberBetween(1, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'outline_seo' => null,
            'checklist_kesiapan' => [
                'keyword_sudah_dicek' => false,
                'metadata_seo_final' => false,
                'referensi_sudah_valid' => false,
                'konten_sudah_dicek' => false,
                'gambar_unggulan_siap' => false,
            ],
            'sudah_diterbitkan' => false,
            'diterbitkan_pada' => null,
        ]);
    }
}
