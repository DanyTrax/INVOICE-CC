<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('process_id')->constrained('users')->nullOnDelete();
            $table->foreignId('radicado_saved_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('regulatory_events', function (Blueprint $table) {
            $table->foreignId('saved_by_user_id')->nullable()->after('submission_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['radicado_saved_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'radicado_saved_by_user_id']);
        });

        Schema::table('regulatory_events', function (Blueprint $table) {
            $table->dropForeign(['saved_by_user_id']);
            $table->dropColumn('saved_by_user_id');
        });
    }
};
