<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerClientNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'client_id',
        'note',
    ];

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
