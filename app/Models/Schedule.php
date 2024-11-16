<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Schedule extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleFactory> */
    use HasFactory;

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function getWorkingHoursForDate(Carbon $date): array|null
    {
        $hours = array_filter([
            $this->{strtolower($date->format('l')) . '_starts_at'},
            $this->{strtolower($date->format('l')) . '_ends_at'},
        ]);

        return !empty($hours) ? $hours : null;
    }
}
