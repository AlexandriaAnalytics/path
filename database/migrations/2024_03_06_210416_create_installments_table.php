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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->integer('total_installments');
            $table->integer('current_installment')->default(1);
            $table->decimal('total_amount', 10, 2);
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('state', []);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
