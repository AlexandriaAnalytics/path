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
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone');
            $table->string('email');
            $table->foreignId('type_of_training_id')->constrained();
            $table->string('street_name');
            $table->integer('street_number');
            $table->string('city');
            $table->string('postcode');
            $table->string('province_or_state');
            $table->foreignId('country_id')->constrained();
            $table->json('sections')->nullable();
            $table->string('files');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};
