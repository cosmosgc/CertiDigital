<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Instructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'cpf_cnpj',
        'signature_image',
    ];

    /* ==========================
       Relationships
    ========================== */

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function courseClasses()
    {
        return $this->hasMany(CourseClass::class);
    }

    public function contracts()
    {
        return $this->hasMany(InstructorContract::class)->orderByDesc('starts_at');
    }

    public function payments()
    {
        return $this->hasMany(InstructorPayment::class);
    }
}
