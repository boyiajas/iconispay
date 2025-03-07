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
            $table->string('id_number')->nullable(); // ID / Passport number, if applicable
            $table->string('registration_number')->nullable(); // Registration number, if applicable
            $table->foreignId('account_type_id'); // Type of account
            $table->foreignId('institution_id');
            $table->string('branch_code')->nullable(); // Branch code
            $table->string('branch_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('my_reference')->nullable(); // My reference for aggregated payments
            $table->string('recipient_reference')->nullable(); // Recipient's reference for aggregated payments
            $table->boolean('authorised')->default(false); // Whether the account is authorised
            $table->string('avs_verified_at')->nullable();
            $table->integer('number_of_authorizer')->nullable(); // Number of authorizers required
            
            // Fields for Account Verification Service (AVS)
            $table->boolean('verified')->default(false); // Whether the account has been verified
            $table->string('verification_status')->nullable(); // Status from AVS (e.g., 'pending', 'failed', 'successful')
            
            // Detailed AVS result fields
            $table->string('account_found')->nullable(); // Whether the account was found
            $table->string('account_open')->nullable(); // Whether the account has been open for 3+ months
            $table->string('account_type_verified')->nullable(); // Account type from the verification (e.g., 'Cheque account')
            $table->string('account_type_match')->nullable(); // Whether the account type matched
            $table->string('branch_code_match')->nullable(); // Whether the branch code matched
            $table->string('holder_name_match')->nullable(); // Whether the account holder's name matched
            $table->string('holder_initials_match')->nullable(); // Whether the account holder's name matched
            $table->string('registration_number_match')->nullable(); // Whether the registration number matched (for juristic entities)
            $table->foreignId('user_id');  // Foreign key to user

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
