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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('file_reference')->index();  //Index for better performance Reference number for the requisition
            $table->string('reason')->nullable();  // Optional reason for the requisition
            $table->string('parties')->nullable();  // Parties involved in the requisition
            $table->string('properties')->nullable();  // Properties related to the requisition
            $table->foreignId('matter_id')->nullable();  // Foreign key to Matter model
            $table->foreignId('firm_account_id')->nullable(); //Foreign key to Firm Account Model
            $table->unsignedBigInteger('status_id')->index();  // Reference to statuses table
            $table->decimal('transaction_value', 15, 2)->default(0);  // Value of the transaction
            $table->string('capturing_status')->nullable();  // Status of capturing
            $table->string('authorization_status')->nullable();  // Status of authorization
            $table->foreignId('authorized_user_id')->nullable()->index();
            $table->string('authorized_at')->nullable();
            $table->string('locked')->nullable();
            $table->string('funding_status')->nullable();  // Status of funding
            $table->string('settlement_status')->nullable();  // Status of settlement
            $table->string('completed_at')->nullable();
            $table->foreignId('created_by')->index(); // User who created the matter
            $table->foreignId('locked_by')->nullable()->index(); // User who created the matter
            $table->string('locked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
