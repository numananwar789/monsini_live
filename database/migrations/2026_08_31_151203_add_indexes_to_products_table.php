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
        Schema::table('dt_product', function (Blueprint $table) {
            // The products DataTable groups by product_style, defaults to
            // ordering by it, and does a whereIn(product_style) lookup for
            // colors on every page load. None of that had an index, so it
            // was forcing a full-table scan on every single request.
            $table->index('product_style');
            $table->index('factory_style');
            $table->index('product_color');
            $table->index('product_size_range');
            $table->index('product_cost');
            $table->index('product_wholesale_price');
            $table->index('product_vendor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dt_product', function (Blueprint $table) {
            $table->dropIndex(['product_style']);
            $table->dropIndex(['factory_style']);
            $table->dropIndex(['product_color']);
            $table->dropIndex(['product_size_range']);
            $table->dropIndex(['product_cost']);
            $table->dropIndex(['product_wholesale_price']);
            $table->dropIndex(['product_vendor_name']);
        });
    }
};
