<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    
    protected $fillable = [
        'name',
        'status',
        'user_id',
        'target_value',
        'current_value',
        'metric_unit',
        'deadline',
        'achieved_at',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'deadline' => 'date',
        'achieved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 1, // Default to active
    ];
    protected $hidden = ['deleted_at'];

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
