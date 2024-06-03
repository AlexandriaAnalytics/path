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
        Schema::create('true_or_falses', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->boolean('true');
            $table->boolean('false');
            $table->foreignId('training_id')->constrained()->references('id')->on('training')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('true_or_falses');
    }
};
