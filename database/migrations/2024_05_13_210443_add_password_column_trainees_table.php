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
        Schema::table('trainees', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            // Eliminar la columna 'email_verified_at'
            $table->dropColumn('email_verified_at');

            // Eliminar la columna 'password'
            $table->dropColumn('password');

            // Eliminar la columna 'remember_token'
            $table->dropColumn('remember_token');

        });
    }
};
