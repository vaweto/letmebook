<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScheduleExclusion extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleExclusionFactory> */
    use HasFactory;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function excludable(): MorphTo
    {
        return $this->morphTo();
    }
}
