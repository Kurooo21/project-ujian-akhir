<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'user_id',
        'order_code',
        'nama_pelanggan',
        'no_hp',
        'alamat',
        'pesanan',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'jenis_belanja',
        'payment_method',
        'payment_status',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
