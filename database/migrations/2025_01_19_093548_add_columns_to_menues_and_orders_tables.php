<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menues_and_orders_tables', function (Blueprint $table) {
            Schema::table('menues', function (Blueprint $table) {
                $table->enum('type', ['restaurant', 'bar'])->default('restaurant')->after('price');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->enum('type', ['restaurant', 'bar'])->default('restaurant')->after('total_price');
                $table->string('customer_name')->nullable()->after('type');
                $table->string('customer_phone')->nullable()->after('customer_name');
                $table->text('address')->nullable()->after('customer_phone');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menues_and_orders_tables', function (Blueprint $table) {
            //
        });
    }
};
