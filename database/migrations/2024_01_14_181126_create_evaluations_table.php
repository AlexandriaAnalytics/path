<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    /**
     * * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id('id_evaluation');
            $table->decimal('score', 9, 2)->nullable();
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_exam');
            $table->unsignedInteger('id_status')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->integer('mark')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->boolean('payed')->default(false);
            $table->text('comment')->nullable();
            $table->boolean('absent')->default(false);
        });
    }

    /**
     * * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
}
