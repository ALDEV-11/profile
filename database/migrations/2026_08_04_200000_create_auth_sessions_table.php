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
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('otp_hash');                          // bcrypt-hashed OTP
            $table->timestamp('expires_at');                     // OTP expiry (5 min)
            $table->unsignedTinyInteger('attempts')->default(0); // failed verification attempts
            $table->timestamp('blocked_until')->nullable();      // 15-min block after 3 fails
            $table->timestamp('last_sent_at')->nullable();       // cooldown for resend (1 min)
            $table->string('ip_address', 45)->nullable();        // requesting IP
            $table->text('user_agent')->nullable();               // requesting UA
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
    }
};
