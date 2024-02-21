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
        Schema::create('candidate_exam', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_exam');
    }
};
