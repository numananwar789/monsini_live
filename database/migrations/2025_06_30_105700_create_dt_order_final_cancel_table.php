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
        Schema::create('dt_order_final_cancel', function (Blueprint $table) {
            $table->integer('final_ID')->primary();
            $table->integer('order_ID')->default(0);
            $table->integer('order_customer_ID')->nullable();
            $table->string('order_customer_name', 255);
            $table->integer('order_vendor_ID');
            $table->string('order_vendor_name', 255);
            $table->integer('order_product_ID');
            $table->string('order_product_style', 255);
            $table->string('order_product_color', 255);
            $table->string('order_product_size', 255);
            $table->integer('order_quantity');
            $table->integer('given_by_invntry')->default(0);
            $table->integer('given_by_onway')->default(0);
            $table->string('order_cost', 255);
            $table->string('order_purchase_price', 255);
            $table->mediumText('order_note');
            $table->string('purchase_id', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('created_at_final')->useCurrent();
            $table->string('onway_vndr_prchs_ids', 1024);
            $table->string('onway_cstmr_prchs_ids', 1024);
            $table->string('order_status', 255)->default('Pending');
            $table->string('order_wear_date', 255)->default('NA');
            $table->string('user_flag', 255)->default('NA');
            $table->string('order_GUID', 2555)->default('NA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dt_order_final_cancel');
    }
};
