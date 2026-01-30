<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VerificationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'certificate_id',
        'ip_address',
        'user_agent',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    /* ==========================
       Relationships
    ========================== */

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }
}
