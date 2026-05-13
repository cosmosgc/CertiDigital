<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_enrollment_id',
        'course_class_id',
        'reference_month',
        'due_date',
        'amount',
        'paid_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'reference_month' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}
