<?php

namespace App\Models;

use Database\Factories\KursusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kursus extends Model
{
    /** @use HasFactory<KursusFactory> */
    use HasFactory;

    protected $table = 'kursus';

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'thumbnail_url',
    ];

    public function materiKursus(): HasMany
    {
        return $this->hasMany(MateriKursus::class, 'kursus_id')->orderBy('urutan');
    }

    public function kuisLms(): HasMany
    {
        return $this->hasMany(KuisLms::class, 'kursus_id')->latest();
    }

    public function bankSoalLms(): HasMany
    {
        return $this->hasMany(BankSoalLms::class, 'kursus_id')->latest();
    }
}
