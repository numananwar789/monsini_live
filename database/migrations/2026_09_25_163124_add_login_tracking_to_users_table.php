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
        Schema::table('users', function (Blueprint $table) {
            $table->string('login_ip', 45)->nullable()->after('user_name');
            $table->string('login_country', 100)->nullable()->after('login_ip');
            $table->string('login_city', 100)->nullable()->after('login_country');
            $table->timestamp('last_login_at')->nullable()->after('login_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_ip', 'login_country', 'login_city', 'last_login_at']);
        });
    }
};
