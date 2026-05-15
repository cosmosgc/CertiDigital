<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_enrollment_trimester_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_enrollment_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('trimester');
            $table->decimal('activity_grade_1', 5, 2)->nullable();
            $table->decimal('activity_grade_2', 5, 2)->nullable();
            $table->decimal('activity_grade_3', 5, 2)->nullable();
            $table->decimal('activities_average', 5, 2)->nullable();
            $table->decimal('au_grade_1', 5, 2)->nullable();
            $table->decimal('au_grade_2', 5, 2)->nullable();
            $table->decimal('au_grade_3', 5, 2)->nullable();
            $table->decimal('au_average', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['course_enrollment_id', 'trimester'], 'enrollment_trimester_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollment_trimester_grades');
    }
};
