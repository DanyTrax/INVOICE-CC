<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('associates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('document_id', 50);
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('category', 80)->default('Titular');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('document_id');
            $table->index('category');
        });

        Schema::create('concepts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('concept_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')->constrained('concepts')->cascadeOnDelete();
            $table->string('category', 80);
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['concept_id', 'category']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->unsignedInteger('consecutive');
            $table->foreignId('associate_id')->constrained('associates')->restrictOnDelete();
            $table->foreignId('concept_id')->constrained('concepts')->restrictOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('concept_prices');
        Schema::dropIfExists('concepts');
        Schema::dropIfExists('associates');
    }
};
