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
        Schema::create('other_payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('other_payment_id')->constrained();
            $table->float('amount');
            $table->string('description');
            $table->string('link_to_ticket');
            $table->foreignId('user_id')->constrained();
            $table->string('status')->default('not valideted');
            $table->timestamp('validated_at')->nullable();
            $table->string('comments')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_payment_details');
    }
};
