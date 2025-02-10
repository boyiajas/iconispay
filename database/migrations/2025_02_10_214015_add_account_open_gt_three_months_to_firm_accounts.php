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
        Schema::table('firm_accounts', function (Blueprint $table) {
            $table->string('account_open_gt_three_months')->nullable()->after('account_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firm_accounts', function (Blueprint $table) {
            $table->dropColumn('account_open_gt_three_months');
        });
    }
};
