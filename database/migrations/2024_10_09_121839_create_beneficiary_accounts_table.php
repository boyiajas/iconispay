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
        Schema::create('beneficiary_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('display_text'); // Display text for the account
            $table->foreignId('category_id'); // Category of the account
            $table->string('account_number'); // Account number
            $table->enum('account_holder_type', ['natural', 'juristic'])->default('natural'); //Natural and 
            $table->string('company_name')->nullable(); // Company name, if applicable
            $table->string('initials')->nullable(); // Company name, if applicable
            $table->string('surname')->nullable(); // Company name, if applicable
            $table->string('registration_number')->nullable(); // Registration number, if applicable
            $table->string('id_number')->nullable(); // ID / Passport number, if applicable
            $table->foreignId('account_type_id'); // Type of account
            $table->foreignId('institution_id');
            $table->string('branch_code')->nullable(); // Branch code
            $table->string('my_reference')->nullable(); // My reference for aggregated payments
            $table->string('recipient_reference')->nullable(); // Recipient's reference for aggregated payments
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
        Schema::dropIfExists('beneficiary_accounts');
    }
};
