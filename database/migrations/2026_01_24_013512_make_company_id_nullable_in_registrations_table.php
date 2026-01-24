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
        Schema::table('registrations', function (Blueprint $table) {
            // Primero eliminar la foreign key
            $table->dropForeign(['company_id']);
            // Hacer la columna nullable
            $table->unsignedBigInteger('company_id')->nullable()->change();
            // Recrear la foreign key
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Eliminar foreign key
            $table->dropForeign(['company_id']);
            // Hacer la columna NOT NULL
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            // Recrear la foreign key
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
};
