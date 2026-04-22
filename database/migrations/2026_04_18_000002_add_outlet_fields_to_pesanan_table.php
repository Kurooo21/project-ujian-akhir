<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable()->after('order_code')->constrained('outlets')->nullOnDelete();
            $table->string('outlet_name')->nullable()->after('outlet_id');
            $table->string('outlet_city', 100)->nullable()->after('outlet_name');
            $table->string('outlet_district', 100)->nullable()->after('outlet_city');
            $table->text('outlet_address_snapshot')->nullable()->after('outlet_district');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_id');
            $table->dropColumn([
                'outlet_name',
                'outlet_city',
                'outlet_district',
                'outlet_address_snapshot',
            ]);
        });
    }
};
