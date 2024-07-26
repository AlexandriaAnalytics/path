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
            $table->foreignId('timetable_id')->nullable()->constrained();
            $table->foreignId('exam_package_id')->nullable()->constrained();
            $table->foreignId('logistic_id')->nullable()->constrained();
            $table->foreignId('payment_to_team_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign('exams_timetable_id_foreign');
            $table->dropColumn('timetable_id');
            $table->dropForeign('exams_exam_package_id_foreign');
            $table->dropColumn('exam_package_id');
            $table->dropForeign('exams_logistic_id_foreign');
            $table->dropColumn('logistic_id');
            $table->dropForeign('exams_payment_to_team_id_foreign');
            $table->dropColumn('payment_to_team_id');
        });
    }
};
