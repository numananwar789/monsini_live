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
        // Schema::table('dt_product', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });

        // Schema::table('dt_order', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });

        // Schema::table('dt_order_allocation', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });

        // Schema::table('dt_order_allocation_cancel', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });
        // Schema::table('dt_order_final', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });
        // Schema::table('dt_order_final_cancel', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });
        // Schema::table('dt_order_history', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });
        // Schema::table('dt_order_history_archive', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });
        // Schema::table('dt_product_archive', function (Blueprint $table) {
        //     $table->json('sub_products')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dt_product', function (Blueprint $table) {
            $table->dropColumn('sub_products');
        });
    }
};
