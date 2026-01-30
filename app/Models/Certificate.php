<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_code',
        'student_id',
        'course_id',
        'instructor_id',
        'issue_date',
        'start_date',
        'end_date',
        'verification_url',
        'qr_code_path',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'start_date' => 'date',
        'end_date'   => 'date',
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

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(VerificationLog::class);
    }
}
