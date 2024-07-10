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
        Schema::table('candidate_answers', function (Blueprint $table) {
            $table->dropColumn('question_id');
            $table->json('question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_answers', function (Blueprint $table) {
            $table->foreignId('question_id');
            $table->dropColumn('question');
        });
    }
};
