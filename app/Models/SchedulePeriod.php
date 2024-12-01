<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulePeriod extends Model
{
    /** @use HasFactory<\Database\Factories\SchedulePeriodFactory> */
    use HasFactory;

    protected $fillable = [
        'schedule_id', 'start_date', 'end_date'
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    // Check if the schedule period is active for a specific date
    public function isActive(Carbon $date): bool
    {
        return $date->between(
            Carbon::parse($this->start_date),
            Carbon::parse($this->end_date)
        );
    }
}
