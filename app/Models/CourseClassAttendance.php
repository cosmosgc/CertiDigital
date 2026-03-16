<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClassAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_class_id',
        'name',
        'attendance_date',
        'duration_hours',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'duration_hours' => 'decimal:2',
    ];

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function records()
    {
        return $this->hasMany(CourseClassAttendanceRecord::class, 'course_class_attendance_id');
    }
}
