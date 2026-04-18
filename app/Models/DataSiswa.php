<?php

namespace App\Models;

use Database\Factories\DataSiswaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataSiswa extends Model
{
    /** @use HasFactory<DataSiswaFactory> */
    use HasFactory;

    public const SISTEM_PEMBAYARAN = [
        'lunas' => 'Lunas',
        'angsuran' => 'Angsuran',
    ];

    public const STATUS_VALIDASI = [
        'pending' => 'Menunggu',
        'validated' => 'Tervalidasi',
        'rejected' => 'Ditolak',
    ];

    public const STATUS_PEMBAYARAN = [
        'belum_bayar' => 'Belum Bayar',
        'sebagian' => 'Sebagian',
        'lunas' => 'Lunas',
    ];

    protected $table = 'data_siswa';

    protected $fillable = [
        'produk_item_id',
        'divalidasi_oleh',
        'nama_lengkap',
        'asal_sekolah',
        'tingkat_kelas',
        'jurusan',
        'nomor_telepon',
        'nama_orang_tua',
        'nomor_telepon_orang_tua',
        'lokasi_belajar',
        'provinsi',
        'kota',
        'program',
        'nama_program',
        'sistem_pembayaran',
        'status_validasi',
        'tanggal_validasi',
        'nomor_invoice',
        'total_invoice',
        'jumlah_pembayaran',
        'sisa_tagihan',
        'status_pembayaran',
        'tanggal_pembayaran',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_validasi' => 'datetime',
            'tanggal_pembayaran' => 'datetime',
            'total_invoice' => 'integer',
            'jumlah_pembayaran' => 'integer',
            'sisa_tagihan' => 'integer',
        ];
    }

    public static function sistemPembayaranOptions(): array
    {
        return self::SISTEM_PEMBAYARAN;
    }

    public static function statusValidasiOptions(): array
    {
        return self::STATUS_VALIDASI;
    }

    public static function statusPembayaranOptions(): array
    {
        return self::STATUS_PEMBAYARAN;
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(ProdukItem::class, 'produk_item_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    public function labelStatusValidasi(): string
    {
        return self::STATUS_VALIDASI[$this->status_validasi] ?? ucfirst($this->status_validasi);
    }

    public function labelStatusPembayaran(): string
    {
        return self::STATUS_PEMBAYARAN[$this->status_pembayaran] ?? ucfirst($this->status_pembayaran);
    }

    public function kelasBadgeStatusValidasi(): string
    {
        return match ($this->status_validasi) {
            'validated' => 'bg-green-lt text-green',
            'rejected' => 'bg-red-lt text-red',
            default => 'bg-yellow-lt text-yellow',
        };
    }

    public function kelasBadgeStatusPembayaran(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'bg-green-lt text-green',
            'sebagian' => 'bg-orange-lt text-orange',
            default => 'bg-secondary-lt text-secondary',
        };
    }
}
