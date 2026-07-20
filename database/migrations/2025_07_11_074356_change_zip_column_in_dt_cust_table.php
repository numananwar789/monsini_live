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
       Schema::table('dt_cust', function (Blueprint $table) {
            $table->string('zip', 20)->change();
        });

        Schema::table('dt_cust', function (Blueprint $table) {
            $table->integer('cust_ID')->autoIncrement()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dt_cust', function (Blueprint $table) {
            //
        });
    }
};
