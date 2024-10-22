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
        Schema::create('matters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by'); // User who created the matter
            $table->unsignedBigInteger('status_id');  // Reference to statuses table
            $table->string('file_reference');
            $table->string('reason');
            $table->string('properties')->nullable();
            $table->string('parties')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matters');
    }
};
