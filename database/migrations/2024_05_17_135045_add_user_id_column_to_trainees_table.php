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
            $table->dropColumn('full_name');
            $table->dropColumn('email');
            $table->dropColumn('email_verified_at');
            $table->dropColumn('password');
            $table->dropColumn('remember_token');
            $table->foreignId('user_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->string('full_name');
            $table->string('email');
            $table->string('email_verified_at');
            $table->string('password');
            $table->string('remember_token');
            $table->dropForeign('trainees_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
};
