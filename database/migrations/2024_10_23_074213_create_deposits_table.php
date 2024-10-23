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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_account_id');  // Foreign key to firm account
            $table->foreignId('requisition_id');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->boolean('funded')->default(false);
            $table->date('deposit_date')->nullable();
            $table->foreignId('user_id');  // Foreign key to user
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
