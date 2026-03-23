<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('concept_catalog_id')->nullable()->constrained('concept_catalogs')->nullOnDelete();
            $table->unsignedInteger('item_position')->default(1);
            $table->string('concept', 500);
            $table->text('scope')->nullable();
            $table->decimal('fee_value', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_items');
    }
};
