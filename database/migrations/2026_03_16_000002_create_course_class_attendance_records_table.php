<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_class_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_class_attendance_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();

            $table->foreign('course_class_attendance_id', 'ccar_attendance_fk')
                ->references('id')
                ->on('course_class_attendances')
                ->cascadeOnDelete();

            $table->foreign('student_id', 'ccar_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();

            $table->unique(['course_class_attendance_id', 'student_id'], 'attendance_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_class_attendance_records');
    }
};
