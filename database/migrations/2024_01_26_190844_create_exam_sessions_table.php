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
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_name');
            $table->foreignId('module_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('scheduled_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('canditate_exam', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete();

            $table->foreignId('examsession_id')
                ->constrained('exam_sessions')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_exam');
        Schema::dropIfExists('exam_sessions');
    }
};
