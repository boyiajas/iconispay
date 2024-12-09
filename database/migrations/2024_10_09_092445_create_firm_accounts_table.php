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
            $table->string('display_text'); //Display name for the account
            $table->enum('account_holder_type', ['natural', 'juristic'])->default('natural'); //Natural and 
            $table->enum('method', ['manual', 'file_upload'])->default('manual');
            $table->string('account_holder');
            $table->string('account_number');
            $table->foreignId('category_id'); // Category of the account (e.g., "Firm trust account")
            $table->string('status')->nullable();
            $table->foreignId('account_type_id'); // Type of account
            $table->foreignId('institution_id'); // Foreign key to Institution model
            $table->string('branch_code')->nullable(); // Branch code
            $table->string('branch_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('initials')->nullable(); // Initials for natural persons
            $table->string('surname')->nullable(); // Surname for natural persons
            $table->string('company_name')->nullable(); // Company name for juristic persons
            $table->string('id_number')->nullable(); // ID or Passport number for natural persons
            $table->string('registration_number')->nullable(); // Registration number for juristic persons
            $table->string('my_reference')->nullable(); // My reference for payments
            $table->string('recipient_reference')->nullable(); // Recipient's reference for payments
            $table->boolean('verified')->default(false); // Whether the account has been verified
            $table->string('verification_status')->nullable(); // Status from AVS (e.g., 'pending', 'failed', 'successful')
            $table->boolean('account_found')->default(false); // Whether the account was found
            $table->boolean('account_open')->default(false); // Whether the account has been open for 3+ months
            $table->string('account_type_verified')->nullable(); // Account type from the verification (e.g., 'Cheque account')
            $table->boolean('account_type_match')->default(false); // Whether the account type matched
            $table->boolean('branch_code_match')->default(false); // Whether the branch code matched
            $table->boolean('holder_name_match')->default(false); // Whether the account holder's name matched
            $table->boolean('holder_initials_match')->default(false); // Whether the initials matched (for natural persons)
            $table->boolean('registration_number_match')->default(false); // Whether the registration number matched (for juristic entities)
            $table->string('avs_verified_at')->nullable(); // AVS verification timestamp
            $table->integer('number_of_authorizer')->nullable(); // Number of authorizers required
            $table->foreignId('user_id'); // Foreign key to user
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
