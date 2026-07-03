<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('instructor_contracts', 'course_id')) {
            Schema::table('instructor_contracts', function (Blueprint $table) {
                $table->foreignId('course_id')->nullable()->after('instructor_id')
                    ->constrained('courses')->nullOnDelete();
            });
        }

        Schema::table('instructor_contracts', function (Blueprint $table) {
            $table->index(['instructor_id', 'course_id', 'starts_at', 'ends_at'], 'instr_contracts_course_idx');
        });
    }

    public function down(): void
    {
        Schema::table('instructor_contracts', function (Blueprint $table) {
            $table->dropIndex('instr_contracts_course_idx');
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });
    }
};
