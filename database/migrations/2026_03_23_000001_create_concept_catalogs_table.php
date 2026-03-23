<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo opcional de conceptos (alimenta por defecto las filas de propuestas).
     */
    public function up(): void
    {
        Schema::create('concept_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('scope')->nullable();
            $table->decimal('default_fee', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concept_catalogs');
    }
};
