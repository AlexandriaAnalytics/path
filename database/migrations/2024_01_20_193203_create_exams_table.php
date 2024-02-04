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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            $table->string('session_name');
            $table->timestamp('scheduled_date');
            $table->string('type');
            $table->integer('maximum_number_of_students');
            $table->string('comments')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('country_exam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained();
            $table->foreignId('country_id')->constrained();
            $table->double('pack_price', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
        Schema::dropIfExists('country_exam');
    }
};
