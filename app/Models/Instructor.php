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
}
