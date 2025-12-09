<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNutritionTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'target_calories',
        'protein',
        'carbs',
        'fats'
    ];

    /**
     * Get the client that owns the nutrition target.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
