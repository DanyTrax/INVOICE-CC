<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_ip_lockouts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('email_attempted', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedSmallInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->unique('ip_address');
            $table->index('locked_until');
            $table->index('last_attempt_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_ip_lockouts');
    }
};
