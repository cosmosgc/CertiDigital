<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedule_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_id')->nullable()->constrained('course_classes')->nullOnDelete();
            $table->string('title');
            $table->string('event_type', 50)->default('other');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedTinyInteger('weekday')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->boolean('is_recurring_weekly')->default(false);
            $table->timestamps();

            $table->index(['event_type', 'start_date']);
            $table->index(['course_class_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_events');
    }
};
