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
        Schema::table('financings', function (Blueprint $table) {
            $table->decimal('exam_amount', 10,2);
            $table->decimal('exam_rigth', 10, 2);   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financings', function (Blueprint $table) {
            $table->dropColumn('total_amount');
            $table->dropColumn('exam_rigth');
        });
    }
};
