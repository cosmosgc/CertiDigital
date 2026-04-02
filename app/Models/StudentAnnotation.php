<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnnotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_class_id',
        'course_class_attendance_record_id',
        'annotation_date',
        'warning_level',
        'notes',
    ];

    protected $casts = [
        'annotation_date' => 'date',
        'warning_level' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function attendanceRecord()
    {
        return $this->belongsTo(CourseClassAttendanceRecord::class, 'course_class_attendance_record_id');
    }
}
