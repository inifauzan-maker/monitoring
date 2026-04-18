<?php

namespace Database\Factories;

use App\Models\DataSiswa;
use App\Models\ProdukItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataSiswa>
 */
class DataSiswaFactory extends Factory
{
    protected $model = DataSiswa::class;

    public function definition(): array
    {
        $totalInvoice = fake()->numberBetween(1000000, 5000000);
        $jumlahPembayaran = fake()->randomElement([0, (int) floor($totalInvoice / 2), $totalInvoice]);
        $sisaTagihan = max($totalInvoice - $jumlahPembayaran, 0);
        $statusPembayaran = $jumlahPembayaran <= 0
            ? 'belum_bayar'
            : ($sisaTagihan > 0 ? 'sebagian' : 'lunas');

        return [
            'produk_item_id' => ProdukItem::factory(),
            'divalidasi_oleh' => User::factory(),
            'nama_lengkap' => fake()->name(),
            'asal_sekolah' => 'SMA '.fake()->city(),
            'tingkat_kelas' => fake()->randomElement(['10', '11', '12', 'Gap Year']),
            'jurusan' => fake()->randomElement(['IPA', 'IPS', 'Desain']),
            'nomor_telepon' => '08'.fake()->numerify('##########'),
            'nama_orang_tua' => fake()->name(),
            'nomor_telepon_orang_tua' => '08'.fake()->numerify('##########'),
            'lokasi_belajar' => fake()->randomElement(['Bandung', 'Jakarta', 'Online']),
            'provinsi' => fake()->randomElement(['Jawa Barat', 'DKI Jakarta']),
            'kota' => fake()->city(),
            'program' => fake()->randomElement(array_keys(ProdukItem::programOptions())),
            'nama_program' => 'Program '.fake()->randomElement(['Reguler', 'Intensif', 'Privat']),
            'sistem_pembayaran' => fake()->randomElement(array_keys(DataSiswa::sistemPembayaranOptions())),
            'status_validasi' => 'validated',
            'tanggal_validasi' => now()->subDay(),
            'nomor_invoice' => 'INV-'.fake()->unique()->numerify('######'),
            'total_invoice' => $totalInvoice,
            'jumlah_pembayaran' => $jumlahPembayaran,
            'sisa_tagihan' => $sisaTagihan,
            'status_pembayaran' => $statusPembayaran,
            'tanggal_pembayaran' => $jumlahPembayaran > 0 ? now() : null,
            'keterangan' => fake()->sentence(),
        ];
    }
}
