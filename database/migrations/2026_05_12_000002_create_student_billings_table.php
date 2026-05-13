<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_enrollment_id')->nullable()->constrained('course_enrollments')->nullOnDelete();
            $table->foreignId('course_class_id')->nullable()->constrained('course_classes')->nullOnDelete();
            $table->date('reference_month');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'canceled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reference_month', 'status']);
            $table->index(['course_class_id', 'reference_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_billings');
    }
};
