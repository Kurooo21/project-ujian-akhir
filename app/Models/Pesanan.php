<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'user_id',
        'nama_pelanggan',
        'no_hp',
        'alamat',
        'pesanan',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'jenis_belanja',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
