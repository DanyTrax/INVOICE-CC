<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('companies')->cascadeOnDelete();
            $table->string('consecutive', 32)->unique();
            $table->date('date');
            $table->string('currency', 3)->default('COP');
            $table->decimal('exchange_rate', 15, 6)->nullable();
            $table->string('status', 32)->default('Pendiente'); // Pendiente | Aprobada
            $table->text('pdf_footer')->nullable();
            $table->decimal('total_professional_fees', 15, 2)->default(0);
            $table->boolean('apply_tax')->default(false);
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->boolean('apply_bank_fee')->default(false);
            $table->decimal('bank_fee_value', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->index(['client_id', 'status']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
