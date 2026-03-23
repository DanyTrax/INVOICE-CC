<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_user', function (Blueprint $table) {
            $table->boolean('sees_all_processes')->default(false)->after('user_id');
        });

        if (Schema::hasColumn('users', 'sees_all_company_processes')) {
            $userIds = DB::table('users')->where('sees_all_company_processes', true)->pluck('id');
            foreach ($userIds as $uid) {
                DB::table('company_user')->where('user_id', $uid)->update(['sees_all_processes' => true]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('sees_all_company_processes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('sees_all_company_processes')->default(false)->after('is_active');
        });

        Schema::table('company_user', function (Blueprint $table) {
            $table->dropColumn('sees_all_processes');
        });
    }
};
