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
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('files_url')->nullable();
            $table->boolean('can_add_candidates');
            $table->string('phone');
            $table->string('email');
            $table->string('street_name');
            $table->string('number')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('postcode');
            $table->string('country');
            $table->decimal('discounted_price_diferencial', 12, 2)->default(0);
            $table->decimal('discounted_price_percentage',12, 2)->default(0);
            $table->decimal('rigth_exam_diferencial', 12, 2)->default(100);


            $table->foreignId('institute_type_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('institute_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institute_id')
                ->constrained('institutes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institute_user');
        Schema::dropIfExists('institutes');
    }
};
