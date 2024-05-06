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
        Schema::create('activity_true_or_false_justifies', function (Blueprint $table) {
            $table->id();
            $table->string('question', 3000);
            $table->boolean('true')->nullable();
            $table->boolean('false')->nullable();
            $table->string('justify', 3000)->nullable();
            $table->foreignId('training_id')->constrained()->references('id')->on('training')->on('training')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_true_or_false_justifies');
    }
};
