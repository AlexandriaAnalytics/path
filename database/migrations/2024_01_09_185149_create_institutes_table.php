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

            $table->foreignId('institute_type_id')->constrained('institute_types')->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->cascadeOnDelete();

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
