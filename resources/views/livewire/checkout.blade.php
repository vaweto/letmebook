<form class="space-y-10" wire:submit="submit">
    <div>
        <h2 class="text-xl font-medium">Here's what you're booking</h2>
        <div class="mt-6 flex space-x-3 bg-slate-100 rounded-lg p-4">
            @if($employee)
                <img src="{{ $employee->profile_photo_url }}" class="rounded-lg size-14 shrink-0">
            @else
                <div class="rounded-lg size-14 bg-slate-200 shrink-0"></div>
            @endif

            <div class="w-full flex justify-between">
                <div>
                    <div class="font-semibold">{{ $service->title }} ({{ $service->duration }} minutes)</div>
                    <div>{{ $employee->name ?? 'Any employee' }}</div>
                </div>
                <div>
                    {{ $service->price }}
                </div>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-medium">1. When for?</h2>
        <input
            x-data
            x-on:select="$wire.setDate($event.detail)"
            x-picker="{
                date: '{{ $form->date }}',
                availability: {{ $this->availabilityJson }}
            }"
            class="mt-6 text-sm bg-slate-100 border-0 rounded-lg px-6 py-4 w-full"
            placeholder="Choose a date"
        >
    </div>

    <div>
        <h2 class="text-xl font-medium">2. Choose a slot</h2>
        <div class="mt-6">
            <div class="grid grid-cols-3 md:grid-cols-5 gap-8">
                @if($this->times->isNotEmpty())
                    @foreach($this->times as $time)
                        <button wire:click="setTime('{{ $time }}')" type="button" @class(['py-3 px-4 text-sm border border-slate-200 rounded-lg text-center hover:bg-gray-50/75 cursor-pointer', 'bg-slate-100 hover:slate-100' => $time === $form->time])>
                            {{ $time }}
                        </button>
                    @endforeach
                @else
                    <p>No slots available for that date</p>
                @endif
            </div>
        </div>
    </div>

    @if($form->time)
        <div>
            <h2 class="text-xl font-medium">2. Your details and book</h2>
            @error('form.time')
            <div class="bg-slate-900 text-white py-4 px-6 rounded-lg mt-3">
                {{ $message }}
            </div>
            @enderror

            <div class="mt-6">
                <div>
                    <label for="name" class="sr-only">Your name</label>
                    <input type="text" name="name" id="name" class="mt-1 text-sm bg-slate-100 border-0 rounded-lg px-6 py-4 w-full" placeholder="Your name" wire:model="form.name">
                    @error('form.name')
                    <div class="mt-2 text-sm font-medium text-red-500">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-3">
                    <label for="email" class="sr-only">Your email</label>
                    <input type="text" name="email" id="email" class="mt-1 text-sm bg-slate-100 border-0 rounded-lg px-6 py-4 w-full" placeholder="Your email" wire:model="form.email">
                    @error('form.email')
                    <div class="mt-2 text-sm font-medium text-red-500">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="mt-6 py-3 px-6 text-sm border border-slate-200 rounded-lg flex flex-col items-center justify-center text-center hover:bg-slate-900 cursor-pointer bg-slate-800 text-white font-medium">
                    Make booking
                </button>
            </div>
        </div>
    @endif
</form>
