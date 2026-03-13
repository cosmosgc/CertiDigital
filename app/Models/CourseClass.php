<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'description',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_enrollments')
            ->withPivot(['course_id', 'progress_percent', 'grade', 'completed'])
            ->withTimestamps();
    }
}
