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
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->string('two_factor_otp')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_otp_expires_at')->nullable()->after('two_factor_otp');
            $table->unsignedTinyInteger('two_factor_attempts')->default(0)->after('two_factor_otp_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_otp',
                'two_factor_otp_expires_at',
                'two_factor_attempts',
            ]);
        });
    }
};
