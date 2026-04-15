<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('order_code', 30)->nullable()->after('user_id')->index();
            $table->string('payment_method', 50)->default('manual')->after('jenis_belanja');
            $table->string('payment_status', 50)->default('Lunas')->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropIndex(['order_code']);
            $table->dropColumn([
                'order_code',
                'payment_method',
                'payment_status',
                'paid_at',
            ]);
        });
    }
};
