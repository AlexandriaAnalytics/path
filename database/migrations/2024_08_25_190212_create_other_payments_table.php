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
        Schema::create('concept_payments', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('other_payments', function (Blueprint $table) {
            $table->id();
            $table->string('names');
            $table->string('surnames');
            $table->date('birth_date');
            $table->string('personal_ID');
            $table->float('amount_to_be_paid');
            $table->float('amount_paid')->default(0);
            $table->string('currency');
            $table->date('limit_date');
            $table->string('link_to_ticket')->nullable();
            $table->foreignId('institute_id')->nullable()->constrained();
            $table->foreignId('candidate_id')->nullable()->constrained();
            $table->string('comments')->nullable();
            $table->boolean('archived')->default(false);
            $table->string('status')->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('concept_other_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_payment_id')->constrained();
            $table->foreignId('other_payment_id')->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concept_other_payments');
        Schema::dropIfExists('other_payments');
        Schema::dropIfExists('concept_payments');
    }
};
