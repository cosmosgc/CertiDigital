<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_class_id')->nullable()->constrained('course_classes')->nullOnDelete();
            $table->foreignId('course_class_attendance_record_id')
                ->nullable()
                ->constrained('course_class_attendance_records')
                ->nullOnDelete();
            $table->date('annotation_date');
            $table->unsignedTinyInteger('warning_level')->default(0);
            $table->text('notes');
            $table->timestamps();

            $table->index(['student_id', 'annotation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_annotations');
    }
};
