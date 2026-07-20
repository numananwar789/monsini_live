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
       Schema::table('dt_vendor', function (Blueprint $table) {
            $table->integer('vendor_ID')->autoIncrement()->change();
        });

        // product_ID
        Schema::table('dt_product', function (Blueprint $table) {
            $table->integer('product_ID')->autoIncrement()->change();
        });

        // product_ID (archive)
        Schema::table('dt_product_archive', function (Blueprint $table) {
            $table->integer('product_ID')->autoIncrement()->change();
        });

        // customer ID
        // Schema::table('dt_customer', function (Blueprint $table) {
        //     $table->integer('cust_ID')->autoIncrement()->change();
        // });

        // inventory
        Schema::table('dt_inventory', function (Blueprint $table) {
            $table->integer('uID')->autoIncrement()->change();
        });

        // orders
        Schema::table('dt_order', function (Blueprint $table) {
            $table->integer('order_ID')->autoIncrement()->change();
        });

        // allocations
        Schema::table('dt_order_allocation', function (Blueprint $table) {
            $table->integer('allocation_ID')->autoIncrement()->change();
        });

        // allocation cancel
        Schema::table('dt_order_allocation_cancel', function (Blueprint $table) {
            $table->integer('allocation_ID')->autoIncrement()->change();
        });

        // order cancel
        Schema::table('dt_order_cancel', function (Blueprint $table) {
            $table->integer('order_ID')->autoIncrement()->change();
        });

        // final
        Schema::table('dt_order_final', function (Blueprint $table) {
            $table->integer('final_ID')->autoIncrement()->change();
        });

        // final cancel
        Schema::table('dt_order_final_cancel', function (Blueprint $table) {
            $table->integer('final_ID')->autoIncrement()->change();
        });

        // history
        Schema::table('dt_order_history', function (Blueprint $table) {
            $table->integer('history_ID')->autoIncrement()->change();
        });

        // history archive
        Schema::table('dt_order_history_archive', function (Blueprint $table) {
            $table->integer('history_ID')->autoIncrement()->change();
        });

        // email body
        Schema::table('email_body', function (Blueprint $table) {
            $table->integer('email_id')->autoIncrement()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
