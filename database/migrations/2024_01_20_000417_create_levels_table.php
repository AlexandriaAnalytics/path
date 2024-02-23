<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLevelsTable extends Migration
{
    /**
     * * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedTinyInteger('minimum_age')
                ->nullable();
            $table->unsignedTinyInteger('maximum_age')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('level_country', function (Blueprint $table) {
            $table->id();

            $table->foreignId('level_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('price_all_modules', 12, 2)
                ->comment('Price for all modules');
            $table->decimal('price_exam_right_all_modules', 12, 2)
                ->comment('Price for exam right for all modules');
            $table->decimal('price_exam_right', 12, 2)
                ->comment('Price for exam right without all modules');

            $table->timestamps();
        });
    }

    /**
     * * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_country');
        Schema::dropIfExists('levels');
    }
}
