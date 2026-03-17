<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClassAttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_class_attendance_id',
        'student_id',
    ];

    public function attendance()
    {
        return $this->belongsTo(CourseClassAttendance::class, 'course_class_attendance_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
