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
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });

        Schema::create('candidate_module', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });

        Schema::create('level_country_module', function (Blueprint $table) {
            $table->id();

            $table->foreignId('level_country_id')
                ->constrained('level_country')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('price', 12, 2);

            $table->timestamps();
        });

        Schema::create('institute_custom_level_price', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institute_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('level_country_id')
                ->constrained('level_country')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('percentage_extra_price_all_modules', 12, 2)
                ->nullable()
                ->comment('Extra price for all modules');
            $table->decimal('percentage_extra_price_exam_right', 12, 2)
                ->nullable()
                ->comment('Extra price for exam right');
            $table->decimal('percentage_extra_price_exam_right_all_modules', 12, 2)
                ->nullable()
                ->comment('Extra price for exam right for all modules');

            $table->decimal('extra_price_all_modules', 12, 2)
                ->nullable()
                ->comment('Extra price for all modules');
            $table->decimal('extra_price_exam_right', 12, 2)
                ->nullable()
                ->comment('Extra price for exam right');
            $table->decimal('extra_price_exam_right_all_modules', 12, 2)
                ->nullable()
                ->comment('Extra price for exam right for all modules');

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
        Schema::dropIfExists('institute_custom_level_price');
        Schema::dropIfExists('level_country_module');
        Schema::dropIfExists('candidate_module');
        Schema::dropIfExists('exam_module');
        Schema::dropIfExists('modules');
    }
}
