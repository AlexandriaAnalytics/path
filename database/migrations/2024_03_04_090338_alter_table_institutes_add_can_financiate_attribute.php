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
        Schema::table('institutes', function (Blueprint $table) {
            $table->boolean('installment_plans')->default(false);
            $table->boolean('internal_payment_administration')->default(false);
            $table->decimal('mora', 10, 2)->default(0);
            $table->integer('expiration_day_inferior')->nullable();
            $table->integer('expiration_dat_superior')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('installment_plans');
            $table->dropColumn('mora');

        });
    }
};
