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
        Schema::create('dt_vendor', function (Blueprint $table) {
            $table->integer('vendor_ID')->primary();
            $table->string('vendor_name', 255)->default('NA');
            $table->string('vendor_comp_name', 255)->default('NA');
            $table->string('vendor_address', 255)->default('NA');
            $table->string('vendor_phone', 155)->default('NA');
            $table->string('vendor_email', 155)->default('NA');
            $table->string('vendor_fax', 155)->default('NA');
            $table->string('vendor_agent', 255)->default('NA');
            $table->longText('message');
            $table->string('vendor_days', 155)->default('NA');
            $table->string('vendor_days_stock', 155)->default('15');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dt_vendor');
    }
};
