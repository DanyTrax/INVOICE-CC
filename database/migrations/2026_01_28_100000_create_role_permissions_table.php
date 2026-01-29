<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('module'); // companies, registrations, users, settings, backups, etc.
            $table->string('action'); // view, create, update, delete
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['role_id', 'module', 'action']);
        });
        
        // Tabla para jerarquía de roles (qué roles puede crear cada rol)
        Schema::create('role_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('can_create_role'); // Nombre del rol que puede crear
            $table->boolean('can_view')->default(true); // Si puede ver usuarios con ese rol
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['role_id', 'can_create_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_hierarchy');
        Schema::dropIfExists('role_permissions');
    }
};
