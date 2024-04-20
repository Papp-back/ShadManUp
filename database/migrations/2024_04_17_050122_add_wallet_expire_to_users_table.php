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
            $table->timestamp('wallet_expire')->nullable()->after('phone_code_send_time');
            $table->string('wallet_gift')->nullable()->after('wallet');
            $table->string('ref_level')->nullable()->after('referral');
            $table->integer('login_level')->default(0)->after('login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_expire');
            $table->dropColumn('ref_level');
            $table->dropColumn('wallet_gift');
            $table->dropColumn('login_level');
        });
    }
};