<?php

use App\Enums\ActivityType;
use App\Enums\TypeQuestion;
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
        Schema::create('training', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description',3000)->nullable();
            $table->enum('question_type', TypeQuestion::values());
            $table->enum('activity_type', ActivityType::values());
            $table->foreignId('section_id')->constrained()->references('id')->on('sections')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training');
    }
};
