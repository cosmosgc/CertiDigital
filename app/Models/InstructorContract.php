<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'payment_type',
        'hourly_rate',
        'monthly_amount',
        'starts_at',
        'ends_at',
        'active',
        'notes',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'active' => 'boolean',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
