<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_class_attendance_records', function (Blueprint $table) {
            $table->decimal('grade', 5, 2)->nullable()->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('course_class_attendance_records', function (Blueprint $table) {
            $table->dropColumn('grade');
        });
    }
};
