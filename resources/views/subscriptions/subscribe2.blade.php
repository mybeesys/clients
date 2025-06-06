@vite('resources/css/app.css')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@lang('main.subscriptions')</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div id="blur-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-lg z-50"></div>
    <div class="relative px-0 py-20 bg-white isolate sm:py-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="absolute inset-x-0 p-0 overflow-hidden -top-3 -z-10 transform-gpu px-36 blur-3xl"
            aria-hidden="true">
            <div class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30"
                style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)">
            </div>
        </div>
        <div class="flex flex-col items-center">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="font-semibold text-indigo-600 text-base/7">@lang('general.pricing')</h2>
                <p class="mt-2 text-5xl font-semibold tracking-tight text-gray-900 text-balance sm:text-6xl">
                    @lang('general.choose_right_plan')
                </p>
            </div>
            <p class="max-w-2xl mx-auto mt-6 text-lg font-medium text-center text-gray-600 text-pretty sm:text-xl/8">
                @lang('general.choose_plan_description')</p>
            <div class="inline-flex mt-10 rounded-md shadow-sm">
                <a href="#" aria-current="page"
                    class="annual-btn px-8 py-3 text-sm font-medium  border border-gray-200 rounded-s-lg text-white bg-gray-900 hover:bg-gray-800 ">
                    @lang('general.annual')
                </a>
                <a href="#"
                    class="monthly-btn px-8 py-3 text-sm font-medium border border-gray-200 rounded-e-lg text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700 focus:z-10 ">
                    @lang('general.monthly')
                </a>
            </div>

        </div>
        @php
            function getPlanName($plan)
            {
                return (app()->getLocale() === 'ar' ? $plan->name_ar : $plan->name) ?? ($plan->name ?? $plan->name_ar);
            }

            function getPlanDescription($plan)
            {
                return (app()->getLocale() === 'ar' ? $plan->description_ar : $plan->description) ??
                    ($plan->description ?? $plan->description_ar);
            }

            function getFeatureName($feature)
            {
                return (app()->getLocale() === 'ar' ? $feature->name_ar : $feature->name) ??
                    ($feature->name ?? $feature->name_ar);
            }
        @endphp

        <div
            class="flex flex-col items-center justify-center max-w-lg mx-auto mt-16 gap-y-6 sm:mt-10 lg:max-w-[1700px] lg:flex-row lg:flex-wrap lg:gap-x-5">
            @foreach (['Year', 'Month'] as $periodType)
                @foreach ($plans->where('periodicity_type', $periodType) as $plan)
                    <form class="{{ strtolower($periodType) }}ly-form hidden" action="{{ url('plan/subscribe') }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $plan->id }}">

                        <div
                            class="plan-{{ $plan->id }} relative p-8 bg-gray-900 shadow-2xl rounded-3xl ring-1 ring-gray-900/10 sm:p-10 w-full lg:w-[475px]">
                            {{-- Plan Header --}}
                            <h3 id="tier-{{ $plan->id }}"
                                class="font-semibold text-right text-indigo-400 text-base/7">
                                {{ getPlanName($plan) }}
                            </h3>

                            {{-- Price Display --}}
                            <p class="flex items-baseline mt-4 gap-x-2">
                                <span
                                    class="plan-price-{{ $plan->id }} text-4xl font-semibold tracking-tight text-white">
                                    @if ($plan->price == $plan->price_after_discount)
                                        {{ (int) $plan->price }} {{ __('general.sar') }}
                                    @else
                                        <span
                                            class="text-gray-400 font-light font-italic line-through decoration-red-600 italic">
                                            {{ (int) $plan->price }}
                                        </span>
                                        <span class="text-5xl">{{ (int) $plan->price_after_discount }}</span>
                                        <span class="text-lg">{{ __('general.sar') }}</span>
                                    @endif
                                </span>

                                {{-- Period Display --}}
                                <span class="plan-periodicity-{{ $plan->id }} text-gray-400">
                                    @if ($plan->discount)
                                        @if ($periodType === 'Year')
                                            {{ $plan->periodicity * 12 - $plan->discount }} {{ __('fields.month') }} +
                                            {{ $plan->discount }}
                                            {{ $plan->discount_period_type === 'Month' ? __('fields.month') : __('fields.year') }}
                                            {{ __('general.free') }}
                                        @else
                                            {{ $plan->periodicity - $plan->discount }} {{ __('fields.month') }} +
                                            {{ $plan->discount }} {{ __('fields.month') }} {{ __('general.free') }}
                                        @endif
                                    @else
                                        {{ $plan->periodicity }} {{ __('fields.' . strtolower($periodType) . '') }}
                                    @endif
                                </span>
                            </p>

                            {{-- Plan Description --}}
                            <p class="mt-6 text-white text-base/7">
                                {{ getPlanDescription($plan) }}
                            </p>

                            {{-- Features List --}}
                            <ul role="list" class="mt-8 space-y-3 text-gray-300 text-sm/6 sm:mt-10">
                                @foreach ($plan->features as $feature)
                                    <li class="flex gap-x-3 text-base">
                                        <svg class="flex-none w-5 h-6 text-indigo-400" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path fill-rule="evenodd"
                                                d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ getFeatureName($feature) }}
                                        @if ($feature->consumable)
                                            : {{ (int) $feature->pivot->charges }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Subscribe Button --}}
                            <button type="submit" aria-describedby="tier-{{ $plan->id }}"
                                data-id="{{ $plan->id }}" @disabled(auth()->user()->company?->subscription?->plan_id == $plan->id)
                                class="subscribe-btn w-full mt-8 block rounded-lg disabled:bg-gray-700 disabled:text-gray-200 bg-indigo-400 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 sm:mt-10">
                                @if (auth()->user()->company->subscription)
                                    @if (auth()->user()->company?->subscription?->plan_id == $plan->id)
                                        @lang('general.current_plan')
                                    @else
                                        @lang('general.switch_plan')
                                    @endif
                                @else
                                    @lang('general.subscribe_now')
                                @endif
                            </button>
                        </div>
                    </form>
                @endforeach
            @endforeach
        </div>
    </div>

    <script>
        let plans = @json($plans);

        $(window).on("load", function() {
            setTimeout(() => {
                hideOverlay();
            }, 200);
        });

        $(document).ready(function() {
            $('.yearly-form').removeClass('hidden')
        });

        function showOverlay() {
            $('#blur-overlay').removeClass('hidden').fadeIn(200);
        }

        function hideOverlay() {
            $('#blur-overlay').fadeOut(200, function() {
                $(this).addClass('hidden');
            });
        }

        $('.annual-btn, .monthly-btn').addClass('transition-all duration-500');

        $('.annual-btn').on('click', function(e) {
            e.preventDefault();
            $(this)
                .addClass('text-white bg-gray-900 hover:bg-gray-800')
                .removeClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.monthly-btn')
                .removeClass('text-white bg-gray-900 hover:bg-gray-800')
                .addClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.yearly-form').removeClass('hidden')
            $('.monthly-form').addClass('hidden')

        });

        $('.monthly-btn').on('click', function(e) {
            e.preventDefault();
            $(this)
                .addClass('text-white bg-gray-900 hover:bg-gray-800')
                .removeClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.annual-btn')
                .removeClass('text-white bg-gray-900 hover:bg-gray-800')
                .addClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.yearly-form').addClass('hidden')
            $('.monthly-form').removeClass('hidden')
        });
    </script>
</body>

</html>
