<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    // Nama tabel di database (tanpa ini Laravel akan mencari "bukus")
    protected $table = 'buku';

    // Kolom yang boleh diisi lewat form
    protected $fillable = [
        'judul',
        'penulis',
        'tahun_terbit',
        'sinopsis',
    ];
}