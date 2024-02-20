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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institute_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('names');
            $table->string('surnames');
            $table->string('cbu')->nullable();
            $table->date('birth_date');
            $table->string('status');
            $table->string('personal_educational_needs')->nullable();
            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
