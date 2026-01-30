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
        'progress_percent',
        'grade',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'grade' => 'decimal:2',
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
}
