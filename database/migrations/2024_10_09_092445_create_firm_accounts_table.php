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
        Schema::create('firm_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('display'); //Display name for the account
            $table->enum('account_holder_type', ['natural', 'juristic'])->default('natural'); //Natural and 
            $table->enum('method', ['manual', 'file_upload'])->default('manual');
            $table->string('account_holder');
            $table->string('account_number');
            $table->foreignId('category_id'); // Category of the account (e.g., "Firm trust account")
            $table->string('status')->nullable();
            $table->foreignId('account_type_id'); // Type of account
            $table->foreignId('institution_id'); // Foreign key to Institution model
            $table->string('branch_code')->nullable(); // Branch code
            $table->boolean('aggregated')->default(false); // Whether the account is aggregated
            $table->boolean('authorised')->default(false); // Whether the account is authorised
            $table->boolean('mandated')->default(false); // Whether the account is mandated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firm_accounts');
    }
};
