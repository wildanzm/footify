<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_of_birth',
        'age',
        'gender',
        'last_education',
        'occupation',
    ];

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}
