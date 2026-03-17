<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'document_id',
    ];

    /* ==========================
       Relationships
    ========================== */

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
                    ->withPivot(['course_class_id', 'progress_hours', 'grade', 'completed'])
                    ->withTimestamps();
    }

    public function classEnrollments()
    {
        return $this->belongsToMany(CourseClass::class, 'course_enrollments')
            ->withPivot(['course_id', 'progress_hours', 'grade', 'completed'])
            ->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
