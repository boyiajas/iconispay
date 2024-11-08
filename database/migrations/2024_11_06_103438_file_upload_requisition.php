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
        // Create the file_upload_requisition pivot table
        Schema::create('file_upload_requisition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_upload_id')->index();
            $table->foreignId('requisition_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the file_upload_requisition pivot table first
        Schema::dropIfExists('file_upload_requisition');
    }
};
