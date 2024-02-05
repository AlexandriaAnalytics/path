<?php

use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            // $table->unsignedBigInteger('candidate_number')
            //     ->autoIncrement()
            //     ->startingValue(1000000)
            //     ->unique();

            $table->foreignId('level_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('status', UserStatus::values())->default(UserStatus::Unpaid);

            $table->enum('type_of_certificate', TypeOfCertificate::values());

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
