<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $guarded = ['id'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function item_keranjang()
    {
        return $this->hasMany(ItemKeranjang::class);
    }

    public function item_pesanan()
    {
        return $this->hasMany(ItemPesanan::class);
    }
}
