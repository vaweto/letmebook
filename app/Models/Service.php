<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'number_of_slots', // New field to handle multiple slots
        'type', // New field to distinguish between single-slot or multi-slot services
    ];

    // Constants for service types
    const TYPE_SINGLE = 'single';
    const TYPE_MULTI = 'multi';

    protected $casts = [
      'price' => MoneyIntegerCast::class
    ];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class);
    }

    public function schedules(): MorphMany
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    public function scheduleExclusions(): MorphMany
    {
        return $this->morphMany(ScheduleExclusion::class, 'excludable');
    }
}
