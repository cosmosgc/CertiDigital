<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->decimal('progress_hours', 8, 2)->default(0)->after('course_class_id');
        });

        DB::statement('
            UPDATE course_enrollments
            INNER JOIN courses ON courses.id = course_enrollments.course_id
            SET course_enrollments.progress_hours = CASE
                WHEN courses.workload_hours IS NULL OR courses.workload_hours = 0 THEN 0
                ELSE ROUND((course_enrollments.progress_percent / 100) * courses.workload_hours, 2)
            END
        ');

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn('progress_percent');
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->integer('progress_percent')->default(0)->after('course_class_id');
        });

        DB::statement('
            UPDATE course_enrollments
            INNER JOIN courses ON courses.id = course_enrollments.course_id
            SET course_enrollments.progress_percent = CASE
                WHEN courses.workload_hours IS NULL OR courses.workload_hours = 0 THEN 0
                ELSE ROUND((course_enrollments.progress_hours / courses.workload_hours) * 100, 0)
            END
        ');

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn('progress_hours');
        });
    }
};
