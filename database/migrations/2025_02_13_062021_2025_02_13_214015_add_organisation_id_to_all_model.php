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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    
        Schema::table('firm_accounts', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    
        Schema::table('beneficiary_accounts', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    
        Schema::table('requisitions', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('requisition_histories', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('file_history_logs', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('authorizers', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('firm_accounts', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('beneficiary_accounts', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('requisition_histories', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('file_uploads', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('file_history_logs', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });

        Schema::table('authorizers', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });
    }
};
