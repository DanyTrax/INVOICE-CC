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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_specialist_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Info Producto
            $table->string('product_name');
            $table->string('registration_number')->nullable();
            $table->enum('status', ['vigente', 'tramite', 'requerimiento', 'vencido'])->default('tramite');
            $table->string('transaction_type')->nullable();
            $table->string('quotation_number')->nullable();
            
            // Fechas Cronograma
            $table->date('client_request_date')->nullable();
            $table->date('radication_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('invima_auto_date')->nullable();
            $table->date('response_limit_date')->nullable();
            $table->date('response_radication_date')->nullable();
            
            // Detalles
            $table->text('client_requirement')->nullable();
            $table->text('invima_requirement')->nullable();
            $table->text('pending_docs')->nullable();
            $table->text('observations')->nullable();
            
            // Sistema
            $table->string('radication_number')->nullable();
            $table->string('key_code')->nullable();
            $table->string('resolution_number')->nullable();
            $table->string('drive_folder_url')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
