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
            $table->foreignId('beneficiary_account_id');
            $table->foreignId('category_id');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('my_reference');
            $table->string('recipient_reference')->nullable();
            $table->foreignId('user_id');  // Add user_id column
            $table->enum('status', ['generated', 'processed', 'failed'])->default(null)->nullable(); //Natural and 
            $table->boolean('authorised')->default(false); // Whether the account is authorised
            $table->boolean('verified')->default(false); // Whether the account has been verified
            $table->string('verification_status')->nullable(); // Status from AVS (e.g., 'pending', 'failed', 'successful')
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
