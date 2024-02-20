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
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('complete_price', 12, 2)->nullable();
            $table->text('modules')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('institute_level', function(Blueprint $table){
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->decimal('institute_diferencial_percentage_price', 12, 2)->default(0);
            $table->decimal('institute_diferencial_aditional_price', 12, 2)->default(0);
            $table->decimal('institute_right_exam', 12, 2)->nullable();
            $table->boolean('can_edit')->default(false);
            $table->timestamps();
        });

        Schema::create('level_country', function(Blueprint $table)
        {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->decimal('price_discounted', 12, 2);
            $table->decimal('price_right_exam', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('levels');
        Schema::dropIfExists('institute_level');
    }
}
