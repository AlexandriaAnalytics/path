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
            $table->string('cbu');
            $table->string('alias');
            $table->string('bank_account_owner');
            $table->string('bank_account_owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn('cbu');
            $table->dropColumn('alias');
            $table->dropColumn('bank_account_owner');
            $table->dropColumn('bank_account_owner_id');
        });
    }
};
