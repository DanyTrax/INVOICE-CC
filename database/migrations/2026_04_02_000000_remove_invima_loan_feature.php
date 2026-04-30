<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotes', 'total_loans')) {
            DB::table('quotes')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $loans = (float) ($row->total_loans ?? 0);
                    if ($loans <= 0) {
                        continue;
                    }
                    $fees = (float) ($row->total_professional_fees ?? 0);
                    DB::table('quotes')->where('id', $row->id)->update([
                        'total_professional_fees' => round($fees + $loans, 2),
                        'total_loans' => 0,
                    ]);
                }
            });
        }

        if (Schema::hasColumn('quote_items', 'is_loan')) {
            DB::table('quote_items')->where('is_loan', true)->update(['is_loan' => false]);
        }

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'allows_loans')) {
                $table->dropColumn('allows_loans');
            }
        });

        Schema::table('quote_items', function (Blueprint $table) {
            if (Schema::hasColumn('quote_items', 'is_loan')) {
                $table->dropColumn('is_loan');
            }
        });

        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'total_loans')) {
                $table->dropColumn('total_loans');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('total_loans', 15, 2)->default(0)->after('total_invima_fees');
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->boolean('is_loan')->default(false)->after('invima_rate_value');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('allows_loans')->default(false)->after('drive_folder_id');
        });
    }
};
