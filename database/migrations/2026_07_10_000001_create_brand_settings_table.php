<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Mi Organización');
            $table->string('nit', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account_type', 80)->nullable();
            $table->string('bank_account_number', 80)->nullable();
            $table->string('support_email')->nullable();
            $table->string('treasurer_signature_path')->nullable();
            $table->string('treasurer_signature_title', 200)->nullable();
            $table->string('invoice_email_subject')->default('Cuenta de cobro');
            $table->text('invoice_email_body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_settings');
    }
};
