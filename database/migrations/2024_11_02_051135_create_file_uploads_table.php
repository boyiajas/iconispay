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
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->decimal('file_size', 10, 2)->nullable(); // File size in KB
            $table->timestamp('generated_at')->useCurrent();
            $table->foreignId('user_id');  // Foreign key to user
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
