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
        Schema::table('multiple_choices', function (Blueprint $table) {
            $table->dropForeign('multiple_choices_question_id_foreign');
            $table->dropColumn('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiple_choices', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
        });
    }
};
