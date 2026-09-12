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
        Schema::table('dt_order', function (Blueprint $table) {
            if (!Schema::hasColumn('dt_order', 'staging_flag')) {
                $table->string('staging_flag', 255)->default('No')->after('order_GUID');
            }
            if (!Schema::hasColumn('dt_order', 'staging_date')) {
                $table->string('staging_date', 255)->default('NA')->after('staging_flag');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dt_order', function (Blueprint $table) {
            if (Schema::hasColumn('dt_order', 'staging_date')) {
                $table->dropColumn('staging_date');
            }
            if (Schema::hasColumn('dt_order', 'staging_flag')) {
                $table->dropColumn('staging_flag');
            }
        });
    }
};
