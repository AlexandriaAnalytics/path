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
        Schema::create('examiner_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('description')->nullable();
            $table->json('aswers')->nullable();
            $table->boolean('open_or_close');
            $table->json('performance')->nullable();
            $table->string('multimedia')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiner_questions');
    }
};
