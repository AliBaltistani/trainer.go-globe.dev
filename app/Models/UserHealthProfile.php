<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHealthProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'allergies',
        'chronic_conditions',
        'fitness_level',
    ];

    protected $casts = [
        'allergies' => 'array',
        'chronic_conditions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
