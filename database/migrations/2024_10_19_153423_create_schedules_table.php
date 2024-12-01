<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->morphs('schedulable'); // Polymorphic relation: 'schedulable_type' and 'schedulable_id'
            $table->time('starts_at');
            $table->time('ends_at');
            $table->date('date')->nullable(); // The specific date for this schedule
            $table->boolean('is_recurring')->default(false); // For recurring schedules
            $table->integer('day_of_week')->nullable(); // For recurring schedules, e.g., "Monday"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
