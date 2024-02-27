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
        Schema::create('financings', function (Blueprint $table) {
            $table->id();

            $table->string('currency');

            $table->foreignId('institute_id')
            ->constrained()
            ->cascadeOnDelete()
            ->cascadeOnUpdate();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('candidate_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('financing_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financings');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_financing_id_foreign');
        });
    }
};
