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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('avatar')->nullable();
            $table->string('referral')->nullable();
            $table->string('referrer')->nullable();
            $table->string('login')->nullable();
            $table->integer('role')->default(0);
            $table->string('cellphone')->nullable();
            $table->string('national_code')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('phone_code')->nullable();
            $table->timestamp('phone_code_send_time')->nullable();
            $table->string('wallet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};