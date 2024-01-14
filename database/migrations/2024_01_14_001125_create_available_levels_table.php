<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailableLevelsTable extends Migration
{
    /**
     * * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('available_levels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_id')
                ->constrained();

            $table->foreignId('level_id')
                ->constrained();

            $table->timestamps();
        });
    }

    /**
     * * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('available_levels');
    }
}
