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
        Schema::create('dt_product_archive', function (Blueprint $table) {
           $table->integer('product_ID')->primary();
            $table->string('product_style', 255);
            $table->string('product_color', 255);
            $table->string('product_size_range', 255);
            $table->string('product_cost', 155);
            $table->string('product_wholesale_price', 155);
            $table->integer('product_vendor_ID');
            $table->string('product_vendor_name', 255);
            $table->string('product_link', 255);
            $table->string('product_image', 255);
            $table->tinyInteger('product_status')->default(1);
            $table->string('archive_name', 655);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dt_product_archive');
    }
};
