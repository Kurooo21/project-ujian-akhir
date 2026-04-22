<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'user_id',
        'order_code',
        'outlet_id',
        'outlet_name',
        'outlet_city',
        'outlet_district',
        'outlet_address_snapshot',
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
        'payment_proof',
        'payment_proof_uploaded_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
