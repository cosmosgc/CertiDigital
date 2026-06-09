<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'reference_month',
        'amount',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'reference_month' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
