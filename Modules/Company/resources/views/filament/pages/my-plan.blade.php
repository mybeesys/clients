<x-filament-panels::page>
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Subscription Information</h2>
        @if ($subscription)
            @php
                $plan = $subscription->plan()->first();
                $features = $plan->features;
            @endphp
            <p class="text-md mb-2"><strong>Subscription id:</strong> {{ $subscription->id }}</p>
            <p class="text-md mb-2"><strong>Subscribe date:</strong>
                {{ \Carbon\Carbon::parse($subscription->created_at)->format('F j, Y') }}</p>
            <p class="text-md mb-2"><strong>Expired date:</strong>
                {{ \Carbon\Carbon::parse($subscription->expired_at)->format('F j, Y') }}</p>
            <p class="text-md mb-2"><strong>Plan id:</strong> {{ $plan->id }}</p>
            <p class="text-md mb-2"><strong>Plan name:</strong> {{ $plan->name }}</p>
            <p class="text-md mb-2"><strong>Plan price:</strong> {{ $plan->price }}</p>
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
</x-filament-panels::page>
