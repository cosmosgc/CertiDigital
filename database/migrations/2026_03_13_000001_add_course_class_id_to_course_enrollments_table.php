<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->foreignId('course_class_id')
                ->nullable()
                ->after('course_id')
                ->constrained('course_classes')
                ->nullOnDelete();

            $table->index(['course_id', 'course_class_id']);
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'course_class_id']);
            $table->dropConstrainedForeignId('course_class_id');
        });
    }
};
