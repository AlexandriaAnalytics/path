<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_module', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::create('candidate_module', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::create('country_module', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->double('price', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_module');
        Schema::dropIfExists('candidate_module');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('country_module');
    }
}
