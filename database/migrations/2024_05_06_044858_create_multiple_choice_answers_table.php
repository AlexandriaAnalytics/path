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
        Schema::create('multiple_choice_answers', function (Blueprint $table) {
            $table->id();
            $table->boolean('check')->default(false);
            $table->string('answer', 3000);
            $table->foreignId('activity_multiple_choice_id')->nullable()->constrained()->references('id')->on('activity_multiple_choices')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_choice_answers');
    }
};
