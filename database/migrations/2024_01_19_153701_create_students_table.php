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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institute_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('email')
                ->nullable()
                ->unique()
                ->comment('Email address for login and notifications');
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('password')
                ->nullable();
            $table->rememberToken();

            $table->string('name')
                ->comment('First name(s)');
            $table->string('surname')
                ->comment('Last name(s)');
            $table->date('birth_date')
                ->comment('Date of birth');

            $table->string('cbu')
                ->nullable()
                ->comment('Clave Bancaria Uniforme (CBU)');

            $table->string('status');

            $table->string('personal_educational_needs')
                ->nullable();

            $table->foreignId('country_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
