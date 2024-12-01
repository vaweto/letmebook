<?php

namespace App\Livewire;

use App\Booking\AvailabilityTransformer;
use App\Booking\Date;
use App\Booking\ServiceAvailability\SingleSlotWithEmployeeAvailability;
use App\Booking\Slot;
use App\Livewire\Forms\CheckoutForm;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Checkout extends Component
{
    public Service $service;

    public ?Employee $employee = null;

    public CheckoutForm $form;

    public function mount()
    {
        $this->form->date = $this->availability->firstAvailableDate()?->date->toDateString() ?? now()->toDateString();
    }

    public function setDate(?string $date)
    {
        if(is_null($date)) {
            return;
        }

        $this->form->date = $date;
    }

    public function setTime(string $time)
    {
        $this->form->time = $time;

        if(!$this->employee) {
            $this->employee = $this->getNextAvailableEmployee();
        }
    }

    #[Computed()]
    public function slots()
    {
        return $this->availability->first(function (Date $date) {
            return $date->date->toDateString() === $this->form->date;
        })?->slots;
    }


    #[Computed()]
    public function times()
    {
        return $this->slots?->map(function (Slot $slot) {
            return $slot->time->toTimeString('minutes');
        })->values();
    }

    #[Computed(persist: true)]
    public function availability()
    {
        return (new SingleSlotWithEmployeeAvailability(
            employees: $this->employee ? collect([$this->employee]) : Employee::get(),
            service: $this->service))
            ->forPeriod(
                now()->startOfDay(),
                now()->addMonths(3)->endOfDay()
            );
    }

    #[Computed]
    public function availabilityJson()
    {
        return new AvailabilityTransformer($this->availability);
    }

    public function submit()
    {
        $this->form->validate();

        unset($this->availability);

        if (!$this->availability->forDate($this->form->date)?->containsSlot($this->form->time)) {
            $this->addError('form.time', 'That slot was taken while you were making your booking. Try another one.');
            return;
        }

        $appointment = $this->createAppointment();

        return redirect()->route('appointments.show', $appointment);
    }

    public function render()
    {
        return view('livewire.checkout');
    }

    private function getNextAvailableEmployee()
    {
        return $this->slots->first(function (Slot $slot) {
            return $slot->time->toTimeString('minutes') === $this->form->time;
        })
            ->employees->first();
    }

    private function createAppointment(): Appointment
    {
        $appointment = Appointment::make(
            $this->form->only('name', 'email') + [
                'starts_at' => $startsAt = Carbon::parse($this->form->date)->setTimeFromTimeString($this->form->time),
                'ends_at' => $startsAt->copy()->addMinutes($this->service->duration),
            ]
        );

        $appointment->employee()->associate($this->employee);
        $appointment->service()->associate($this->service);

        $appointment->save();

        return $appointment;
    }
}
