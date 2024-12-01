<?php

namespace Tests\Unit\Booking;

use App\Booking\ScheduleAvailability\ScheduleAvailabilityCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spatie\Period\PeriodCollection;

class ScheduleAvailabilityCalculatorTest extends TestCase
{
    private ScheduleAvailabilityCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ScheduleAvailabilityCalculator();
    }

    #[Test]
    public function initializes_empty_periods(): void
    {
        $this->assertInstanceOf(PeriodCollection::class, $this->calculator->getPeriods());
        $this->assertCount(0, $this->calculator->getPeriods());
    }

    #[Test]
    public function adds_single_availability_period(): void
    {
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();

        $this->calculator->addAvailabilityPeriod($start, $end);

        $this->assertCount(1, $this->calculator->getPeriods());
    }

    #[Test]
    public function adds_multiple_non_overlapping_availability_periods(): void
    {
        $start1 = Carbon::now()->startOfDay();
        $end1 = Carbon::now()->startOfDay()->addHours(4);

        $start2 = Carbon::now()->startOfDay()->addHours(6);
        $end2 = Carbon::now()->startOfDay()->addHours(8);

        $this->calculator->addAvailabilityPeriod($start1, $end1);
        $this->calculator->addAvailabilityPeriod($start2, $end2);

        $this->assertCount(2, $this->calculator->getPeriods());
    }

    #[Test]
    public function merges_overlapping_availability_periods(): void
    {
        $this->markTestSkipped('overlapping must resolve');
        $start1 = Carbon::now()->startOfDay();
        $end1 = Carbon::now()->startOfDay()->addHours(6);

        $start2 = Carbon::now()->startOfDay()->addHours(4);
        $end2 = Carbon::now()->startOfDay()->addHours(8);

        $this->calculator->addAvailabilityPeriod($start1, $end1);
        $this->calculator->addAvailabilityPeriod($start2, $end2);

        $this->assertCount(1, $this->calculator->getPeriods());
    }

    #[Test]
    public function subtracts_exclusion_period_completely_within_availability(): void
    {
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->startOfDay()->addHours(6);

        $exclusionStart = Carbon::now()->startOfDay()->addHours(2);
        $exclusionEnd = Carbon::now()->startOfDay()->addHours(4);

        $this->calculator->addAvailabilityPeriod($start, $end);
        $this->calculator->subtractExclusionPeriod($exclusionStart, $exclusionEnd);

        $this->assertCount(2, $this->calculator->getPeriods());
    }

    #[Test]
    public function subtracts_exclusion_period_partially_overlapping_start(): void
    {
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->startOfDay()->addHours(6);

        $exclusionStart = Carbon::now()->startOfDay()->subHours(2);
        $exclusionEnd = Carbon::now()->startOfDay()->addHours(2);

        $this->calculator->addAvailabilityPeriod($start, $end);
        $this->calculator->subtractExclusionPeriod($exclusionStart, $exclusionEnd);

        $this->assertCount(1, $this->calculator->getPeriods());
    }

    #[Test]
    public function subtracts_exclusion_period_partially_overlapping_end(): void
    {
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->startOfDay()->addHours(6);

        $exclusionStart = Carbon::now()->startOfDay()->addHours(4);
        $exclusionEnd = Carbon::now()->startOfDay()->addHours(8);

        $this->calculator->addAvailabilityPeriod($start, $end);
        $this->calculator->subtractExclusionPeriod($exclusionStart, $exclusionEnd);

        $this->assertCount(1, $this->calculator->getPeriods());
    }

    #[Test]
    public function excludes_time_passed_today(): void
    {
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();

        $this->calculator->addAvailabilityPeriod($start, $end);
        $this->calculator->excludeTimePassedToday();
        $this->assertNotEmpty($this->calculator->getPeriods());
        $this->assertTrue(collect($this->calculator->getPeriods())->first()->startsAfterOrAt(Carbon::now()));
    }

    #[Test]
    public function processes_date_range(): void
    {
        $startAt = Carbon::now()->startOfDay();
        $endsAt = Carbon::now()->startOfDay()->addDays(3);

        $datesProcessed = [];
        $this->calculator->processDateRange($startAt, $endsAt, function (Carbon $date) use (&$datesProcessed) {
            $datesProcessed[] = $date->toDateString();
        });

        $this->assertCount(4, $datesProcessed);
        $this->assertEquals($startAt->toDateString(), $datesProcessed[0]);
        $this->assertEquals($endsAt->toDateString(), end($datesProcessed));
    }
}
