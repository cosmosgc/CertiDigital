<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'name',
        'description',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_enrollments')
            ->withPivot(['course_id', 'progress_hours', 'grade', 'completed'])
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(CourseClassAttendance::class)->orderBy('attendance_date')->orderBy('id');
    }

    public function scheduleEvents()
    {
        return $this->hasMany(ScheduleEvent::class)->orderBy('start_date')->orderBy('start_time');
    }
}
