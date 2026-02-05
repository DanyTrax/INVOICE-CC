<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eventos INVIMA: Autos, Resoluciones, Oficios. due_date para AUTO = 90 días hábiles desde notification_date (lógica en app).
     */
    public function up(): void
    {
        Schema::create('regulatory_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->string('event_type', 32); // AUTO | RESOLUCION | OFICIO
            $table->string('document_number', 64)->nullable(); // Número Auto/Resolución
            $table->date('event_date')->nullable();
            $table->date('notification_date')->nullable();
            $table->date('due_date')->nullable();             // AUTO: 90 días hábiles desde notificación
            $table->string('resolution_key', 64)->nullable(); // Llave (solo si es Resolución)
            $table->string('file_path')->nullable();          // Ruta del PDF
            $table->timestamps();
        });

        Schema::table('regulatory_events', function (Blueprint $table) {
            $table->index(['submission_id', 'event_type']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulatory_events');
    }
};
