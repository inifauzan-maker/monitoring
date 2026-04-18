<?php

namespace App\Models;

use Database\Factories\KategoriArtikelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriArtikel extends Model
{
    /** @use HasFactory<KategoriArtikelFactory> */
    use HasFactory;

    protected $table = 'kategori_artikel';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
    ];

    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class, 'kategori_artikel_id');
    }
}
