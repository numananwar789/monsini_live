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
        Schema::create('dt_cust', function (Blueprint $table) {
            $table->integer('cust_ID')->primary();
            $table->string('f_name', 255);
            $table->string('l_name', 255);
            $table->string('cust_username', 255);
            $table->string('cust_password', 255);
            $table->string('cust_comp_name', 255)->default('NA');
            $table->string('cust_address', 255)->default('NA');
            $table->string('country', 255);
            $table->integer('zip');
            $table->string('cust_phone', 155)->default('NA');
            $table->string('cust_email', 155)->default('NA');
            $table->string('cust_fax', 155)->nullable()->default('NA');
            $table->string('cust_sales_rep', 255)->default('NA');
            $table->string('cust_status', 255)->default('not_allow');
            $table->string('cust_owner', 255)->default('NA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dt_cust');
    }
};
