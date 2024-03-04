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
        Schema::create('modalities', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('level_modality', function(Blueprint $table){
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('modality_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('level_certificate_type', function(Blueprint $table){
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('certificate_type_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       

        Schema::dropIfExists ('level_certificate_type');
        Schema::dropIfExists('level_modality');
        Schema::dropIfExists('modalities');
        Schema::dropIfExists('certificate_types');
    }
};
