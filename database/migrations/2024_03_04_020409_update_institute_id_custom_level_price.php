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
        Schema::table('institute_custom_level_price', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institute_custom_level_price', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable(false)->change();
        });
    }
};
