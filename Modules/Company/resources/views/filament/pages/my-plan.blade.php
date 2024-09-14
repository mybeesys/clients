<x-filament-panels::page>
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Subscription Information</h2>
        @if ($subscription)
            @php
                $current_plan = $subscription->plan()->first();
                $features = $current_plan->features;
            @endphp
            <p class="text-md mb-2"><strong>Subscription id:</strong> {{ $subscription->id }}</p>
            <p class="text-md mb-2"><strong>Subscribe date:</strong>
                {{ \Carbon\Carbon::parse($subscription->created_at)->format('F j, Y') }}</p>
            <p class="text-md mb-2"><strong>Expired date:</strong>
                {{ \Carbon\Carbon::parse($subscription->expired_at)->format('F j, Y') }}</p>
            <p class="text-md mb-2"><strong>Plan id:</strong> {{ $current_plan->id }}</p>
            <p class="text-md mb-2"><strong>Plan name:</strong> {{ $current_plan->name }}</p>
            <p class="text-md mb-2"><strong>Plan price:</strong> {{ $current_plan->price }}</p>
            <p class="text-md font-semibold mb-2">Features:</p>
            <ul class="list-disc list-inside ml-4">
                @foreach ($features as $feature)
                    <li class="mb-1">
                        <span class="font-medium">{{ $feature->name }}</span>
                        <span class="text-gray-600">charges: {{ $feature->pivot->charges }}</span>
                        <span class="text-gray-600">value: {{ $feature->pivot->value }}</span>
                    </li>
                @endforeach
            </ul>

        @else
            <p class="text-red-500">No active subscription.</p>
        @endif
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Available Plans</h2>
        <ul>
            @foreach ($plans as $plan)
                <li class="mb-2">
                    <div class="bg-gray-100 p-4 rounded-lg shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                                <p class="text-sm text-gray-600">${{ $plan->price }}/{{ $plan->periodicity_type }}
                                </p>

                                <ul class="list-unstyled mt-3 mb-4">
                                    <h3 class="text-lg font-semibold">Features</h3>
                                    @foreach ($plan->features as $feature)
                                        <li>{{ $feature->name }}: {{ $feature->pivot->value }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            @if (isset($current_plan) && $current_plan->id === $plan->id)
                                <x-filament::button color="secondary" size="md" disabled>
                                    This is your current plan
                                </x-filament::button>
                            @else
                                <x-filament::button color="secondary" size="md"
                                    class="relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                                    wire:click="switchPlan({{ $plan->id }})">
                                    Switch to this plan
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</x-filament-panels::page>
