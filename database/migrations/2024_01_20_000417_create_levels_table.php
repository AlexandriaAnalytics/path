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

            $table->string('slug');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('complete_price', 12, 2);
            $table->text('modules')->nullable();
            $table->unsignedInteger('tier')->nullable();
            $table->decimal('minimum_age', 3, 1);
            $table->decimal('maximum_age', 3, 1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('levels');
    }
}
