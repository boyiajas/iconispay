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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_account_id');  // Foreign key to firm account
            $table->foreignId('requisition_id');
            $table->foreignId('category_id');
            $table->enum('account_holder_type', ['natural', 'juristic'])->default('natural'); //Natural and 
            $table->string('initials')->nullable();
            $table->string('surname')->nullable();
            $table->string('company_name')->nullable();
            $table->string('registration_number')->nullable(); // Registration number, if applicable
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('my_reference');
            $table->string('recipient_reference')->nullable();
            $table->foreignId('institution_id');
            $table->foreignId('account_type_id'); //savings, cheque, e.t.c
            $table->string('branch_code')->nullable();
            $table->string('id_number')->nullable();
            $table->foreignId('user_id');  // Add user_id column
            $table->boolean('authorised')->default(false); // Whether the account is authorised
            
            // Fields for Account Verification Service (AVS)
            $table->boolean('verified')->default(false); // Whether the account has been verified
            $table->string('verification_status')->nullable(); // Status from AVS (e.g., 'pending', 'failed', 'successful')
            
            // Detailed AVS result fields
            $table->boolean('account_found')->default(false); // Whether the account was found
            $table->boolean('account_open')->default(false); // Whether the account has been open for 3+ months
            $table->string('account_type_verified')->nullable(); // Account type from the verification (e.g., 'Cheque account')
            $table->boolean('account_type_match')->default(false); // Whether the account type matched
            $table->boolean('branch_code_match')->default(false); // Whether the branch code matched
            $table->boolean('holder_name_match')->default(false); // Whether the account holder's name matched
            $table->boolean('holder_initials_match')->default(false); // Whether the account holder's name matched
            $table->boolean('registration_number_match')->default(false); // Whether the registration number matched (for juristic entities)
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
