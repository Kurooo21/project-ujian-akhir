<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_pelanggan');
            $table->string('no_hp', 20);
            $table->text('alamat');
            $table->string('pesanan');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 10, 2);
            $table->decimal('total_harga', 10, 2);
            $table->string('jenis_belanja', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
