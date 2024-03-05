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
        Schema::create('custom_module_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('custom_level_price_id')
                ->constrained('custom_level_price')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('module_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('price', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_module_prices');
    }
};
