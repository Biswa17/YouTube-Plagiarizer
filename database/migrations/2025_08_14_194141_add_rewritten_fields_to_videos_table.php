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
        Schema::table('videos', function (Blueprint $table) {
            $table->string('rewritten_transcript_path')->nullable()->after('rewritten_script');
            $table->timestamp('rewritten_at')->nullable()->after('rewritten_transcript_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('rewritten_transcript_path');
            $table->dropColumn('rewritten_at');
        });
    }
};
