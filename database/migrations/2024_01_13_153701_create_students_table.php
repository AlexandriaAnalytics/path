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

            $table->string('national_id', 32);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('slug')->nullable();
            $table->string('country');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('cbu')->nullable();
            $table->date('birth_date');
            $table->string('status');

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
