<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_class_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_attendance_id')->constrained('course_class_attendances')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_class_attendance_id', 'student_id'], 'attendance_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_class_attendance_records');
    }
};
