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
        Schema::create('upload_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ocr_file_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45);
            $table->string('original_filename');
            $table->string('file_hash', 64)->nullable();
            $table->unsignedBigInteger('file_size');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cached'])->default('pending');
            $table->boolean('from_cache')->default(false);
            $table->text('error_message')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->timestamps();
            
            $table->index(['ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_logs');
    }
};

