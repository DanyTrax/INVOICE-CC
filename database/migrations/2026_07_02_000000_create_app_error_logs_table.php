<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->default('error');
            $table->string('exception_class')->nullable();
            $table->text('message');
            $table->string('file', 1024)->nullable();
            $table->integer('line')->nullable();
            $table->string('url', 1024)->nullable();
            $table->string('method', 10)->nullable();
            $table->unsignedInteger('status_code')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('ip', 45)->nullable();
            $table->longText('trace')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_error_logs');
    }
};
