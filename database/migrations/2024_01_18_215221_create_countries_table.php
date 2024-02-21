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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('name', 40)->unique();
            $table->string('slug', 50);
            $table->string('monetary_unit');
            $table->string('monetary_unit_symbol', 5);

            $table->timestamps();
        });

        // create intermediate table for countries and payment_methods
        Schema::create('country_payment_method', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('payment_method_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_payment_method');
        Schema::dropIfExists('countries');
    }
};
