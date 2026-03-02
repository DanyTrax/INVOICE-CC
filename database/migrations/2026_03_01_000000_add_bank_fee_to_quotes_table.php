<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotes', 'apply_bank_fee')) {
            return;
        }
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('apply_bank_fee')->default(false)->after('tax_percentage');
            $table->decimal('bank_fee_value', 12, 2)->nullable()->after('apply_bank_fee');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('quotes', 'apply_bank_fee')) {
            return;
        }
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['apply_bank_fee', 'bank_fee_value']);
        });
    }
};
