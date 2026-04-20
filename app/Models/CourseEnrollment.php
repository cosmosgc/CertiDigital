<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'course_class_id',
        'progress_hours',
        'grade',
        'completed',
        'start_date',
        'end_date',
        'frozen',
    ];

    protected $casts = [
        'progress_hours' => 'decimal:2',
        'completed' => 'boolean',
        'grade' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'frozen' => 'boolean',
    ];

    /* ==========================
       Relationships
    ========================== */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
