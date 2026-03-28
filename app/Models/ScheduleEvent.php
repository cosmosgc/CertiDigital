<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_class_id',
        'title',
        'event_type',
        'description',
        'location',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'weekday',
        'is_all_day',
        'is_recurring_weekly',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_all_day' => 'boolean',
        'is_recurring_weekly' => 'boolean',
        'weekday' => 'integer',
    ];

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
