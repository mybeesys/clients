@php
    use Illuminate\Support\Number;

    $plans = $plans ?? collect();
    $localeIsAr = app()->getLocale() === 'ar';
    $planName = fn ($plan) => $localeIsAr ? ($plan->name_ar ?: $plan->name) : ($plan->name ?: $plan->name_ar);
    $planDescription = fn ($plan) => $localeIsAr
        ? ($plan->description_ar ?: $plan->description)
        : ($plan->description ?: $plan->description_ar);
    $periodLabel = fn (string $type) => match (strtolower($type)) {
        'year' => __('fields.year'),
        'month' => __('fields.month'),
        default => $type,
    };
@endphp

<div
    x-data="{
        selected: $wire.$entangle('data.subscription.plan_id'),
        select(id) {
            this.selected = this.selected == id ? null : id;
        },
    }"
    class="space-y-4"
>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($plans as $plan)
            @php
                $hasDiscount = filled($plan->price_after_discount)
                    && (float) $plan->price_after_discount < (float) $plan->price;
                $displayPrice = $hasDiscount ? $plan->price_after_discount : $plan->price;
            @endphp
            <button
                type="button"
                x-on:click="select({{ $plan->id }})"
                :class="selected == {{ $plan->id }}
                    ? 'border-primary-600 bg-primary-50 ring-2 ring-primary-600/20 dark:border-primary-500 dark:bg-primary-950/40'
                    : 'border-gray-200 bg-white hover:border-primary-300 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-600'"
                class="group relative flex h-full flex-col rounded-xl border p-5 text-start shadow-sm transition"
            >
                <span
                    x-show="selected == {{ $plan->id }}"
                    class="absolute end-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-white"
                >
                    <x-heroicon-s-check class="h-4 w-4" />
                </span>

                <p class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">
                    {{ $periodLabel($plan->periodicity_type) }}
                </p>
                <h3 class="mt-1 text-lg font-bold text-gray-950 dark:text-white">
                    {{ $planName($plan) }}
                </h3>

                @if ($planDescription($plan))
                    <p class="mt-2 line-clamp-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ $planDescription($plan) }}
                    </p>
                @endif

                <div class="mt-4 flex flex-wrap items-baseline gap-2">
                    @if ($hasDiscount)
                        <span class="text-sm text-gray-400 line-through decoration-red-500">
                            {{ Number::format((float) $plan->price, precision: 0) }}
                        </span>
                    @endif
                    <span class="text-3xl font-bold text-gray-950 dark:text-white">
                        {{ Number::format((float) $displayPrice, precision: 0) }}
                    </span>
                    <span class="text-sm font-medium text-gray-500">{{ __('general.sar') }}</span>
                </div>

                @if ((int) $plan->grace_days > 0)
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('main.wizard.plan_grace_days', ['days' => $plan->grace_days]) }}
                    </p>
                @endif
            </button>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
                <p class="text-sm text-gray-500">{{ __('main.wizard.no_plans_available') }}</p>
            </div>
        @endforelse
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        {{ __('main.wizard.subscription_plan_optional_hint') }}
    </p>
</div>
