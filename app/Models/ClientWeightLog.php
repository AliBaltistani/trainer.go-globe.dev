<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientWeightLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
        'unit',
        'logged_at',
        'notes',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'logged_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
