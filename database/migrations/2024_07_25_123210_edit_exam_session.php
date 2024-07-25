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
        Schema::table('exams', function (Blueprint $table) {
            $table->timestamp('scheduled_date')->nullable()->change();
            $table->timestamp('payment_deadline')->nullable()->change();
            $table->foreignId('institute_type_id')->nullable()->constrained();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->timestamp('scheduled_date')->nullable(false)->change();
            $table->timestamp('payment_deadline')->nullable(false)->change();
            $table->dropForeign('exams_institute_type_id_foreign');
            $table->dropColumn('institute_type_id');
            $table->dropColumn('status');
        });
    }
};
