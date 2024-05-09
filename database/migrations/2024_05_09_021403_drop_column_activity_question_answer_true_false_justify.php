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
        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropColumn('answer');
        });

        Schema::table('activity_true_or_false_justifies', function (Blueprint $table) {
            $table->dropColumn('justify');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->string('answer', 5000);
        });

        Schema::table('activity_true_or_false_justifies', function (Blueprint $table) {
            $table->string('justify', 3000)->nullable();
        });     
    }
};
