<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Schedule extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'schedulable_type',
        'schedulable_id',
        'starts_at',
        'ends_at',
        'date',
        'is_recurring',
        'day_of_week',
    ];

    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getWorkingHoursForDate($date)
    {
        if ($this->is_recurring && (int)$this->day_of_week === $date->weekday()) {

            return [$this->starts_at, $this->ends_at];
        }

        if ($this->date === $date->toDateString()) {
            return [$this->starts_at, $this->ends_at];
        }

        return null;
    }

    public function periods(): HasMany
    {
        return $this->hasMany(SchedulePeriod::class);
    }
}
