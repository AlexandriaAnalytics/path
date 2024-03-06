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
        Schema::table('payments', function (Blueprint $table) {
            $table->date('paid_date')->nullable();
            $table->date('current_period');
            $table->string('link_to_ticket')->nullable();
            $table->foreignId('institute_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('candidate_id')->nullable()->change();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('paid_date');
            $table->dropColumn('current_period');
            $table->dropForeign('institute_id');
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate()->change();
        });
    }
};
