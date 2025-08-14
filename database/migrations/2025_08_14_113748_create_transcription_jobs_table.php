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
        Schema::create('transcription_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_url', 500);
            $table->string('original_title')->nullable();
            $table->string('audio_file_path')->nullable();
            $table->longText('transcription_text')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('status', ['completed', 'failed']);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcription_jobs');
    }
};
