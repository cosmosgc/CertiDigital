<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseEnrollmentTrimesterGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_enrollment_id',
        'trimester',
        'activity_grade_1',
        'activity_grade_2',
        'activity_grade_3',
        'activities_average',
        'au_grade_1',
        'au_grade_2',
        'au_grade_3',
        'au_average',
        'final_grade',
    ];

    protected $casts = [
        'activity_grade_1' => 'decimal:2',
        'activity_grade_2' => 'decimal:2',
        'activity_grade_3' => 'decimal:2',
        'activities_average' => 'decimal:2',
        'au_grade_1' => 'decimal:2',
        'au_grade_2' => 'decimal:2',
        'au_grade_3' => 'decimal:2',
        'au_average' => 'decimal:2',
        'final_grade' => 'decimal:2',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }
}
