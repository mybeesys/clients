@vite('resources/css/app.css')
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@lang('main.subscriptions')</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cairo:wght@200..1000&display=swap"
        rel="stylesheet">
    <style>
        html {
            font-family: "Cairo", serif;
        }
    </style>
</head>

<body dir="{{ session('locale') === 'ar' ? 'rtl' : 'ltr' }}">
    <div id="blur-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-lg z-50"></div>
    <div class="px-5 mt-4 mb-3 mx-5">
        <a href="{{ session('locale') === 'ar' ? url('set-locale/en') : url('set-locale/ar') }}"
            class="bg-slate-700 px-3 pb-3 pt-2 rounded-xl text-white"> {{ session('locale') === 'ar' ? 'English' : 'عربي'}}</a>
    </div>
    <div class="absolute inset-x-0 p-0 overflow-hidden -top-3 -z-10 transform-gpu px-36 blur-3xl" aria-hidden="true">
        <div class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30"
            style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)">
        </div>
    </div>
    <div class="flex flex-col items-center mb-10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="font-semibold text-red-800 text-lg">@lang('general.pricing')</h2>
            <p class="mt-2 text-5xl font-semibold tracking-tight text-gray-900 text-balance sm:text-6xl">
                @lang('general.choose_right_plan')
            </p>
        </div>
        <p class="max-w-2xl mx-auto mt-6 text-lg font-medium text-center text-gray-600 text-pretty sm:text-xl/8">
            @lang('general.choose_plan_description')</p>
        <div class="inline-flex mt-10 rounded-md shadow-sm">
            <a href="#" aria-current="page"
                class="annual-btn px-8 py-3 text-sm font-medium  border border-gray-200 rounded-s-lg text-white bg-slate-800 hover:bg-slate-900 ">
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
    <table class="w-full mx-auto overflow-x-auto table-auto max-w-7xl shadow-lg rounded-2xl m-12">
        @foreach (['Year', 'Month'] as $periodType)
            <thead class="{{ strtolower($periodType) }}ly-form hidden">
                <tr @class(["bg-gradient-to-r ", 'from-slate-900 to-slate-700' => session('locale') === 'en', 'from-slate-700 to-slate-900' => session('locale') === 'ar'])>
                    <th @class([
                        'p-6 transition-colors border-b text-white hover:bg-slate-800 text-2xl shadow-lg min-w-52',
                        'rounded-tr-2xl' => session('locale') === 'ar',
                        'rounded-tl-2xl' => session('locale') === 'en',
                    ])>
                        @lang('main.features')
                    </th>
                    @foreach ($plans->where('periodicity_type', $periodType) as $plan)
                        <form class="{{ strtolower($periodType) }}ly-form" action="{{ url('plan/subscribe') }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $plan->id }}">

                            <th @class([
                                'p-6 transition-colors hover:bg-slate-800 shadow-2xl min-w-96',
                                'hover:rounded-tl-2xl rounded-tl-2xl' => $loop->last && session('locale') === 'ar',
                                'hover:rounded-tr-2xl rounded-tr-2xl' => $loop->last && session('locale') === 'en',
                            ])>
                                <h3 id="tier-{{ $plan->id }}"
                                    class="font-bold text-right text-gray-200 text-2xl mb-5">
                                    {{ getPlanName($plan) }}
                                </h3>

                                {{-- Price Display --}}
                                <p class="flex flex-col mt-4 gap-x-2 gap-y-4">
                                    <span
                                        class="plan-price-{{ $plan->id }} text-start text-4xl tracking-tight text-gray-100 font-bold">
                                        @if ($plan->price == $plan->price_after_discount)
                                            <span class="text-5xl">{{ (int) $plan->price }}</span> <span
                                                class="text-lg">{{ __('general.sar') }}</span>
                                        @else
                                            <span
                                                class="ml-2 text-gray-300 font-semibold font-italic line-through decoration-red-600 italic">
                                                {{ (int) $plan->price }}
                                            </span>
                                            <span class="text-5xl">{{ (int) $plan->price_after_discount }}</span>
                                            <span class="text-lg">{{ __('general.sar') }}</span>
                                        @endif
                                    </span>

                                    {{-- Period Display --}}
                                    <span
                                        class="plan-periodicity-{{ $plan->id }} text-gray-100 font-semibold text-start">
                                        @if ($plan->discount)
                                            @if ($periodType === 'Year')
                                                {{ $plan->periodicity * 12 - $plan->discount }}
                                                {{ __('fields.month') }} +
                                                {{ $plan->discount }}
                                                {{ $plan->discount_period_type === 'Month' ? __('fields.month') : __('fields.year') }}
                                                {{ __('general.free') }}
                                            @else
                                                {{ $plan->periodicity - $plan->discount }} {{ __('fields.month') }} +
                                                {{ $plan->discount }} {{ __('fields.month') }}
                                                {{ __('general.free') }}
                                            @endif
                                        @else
                                            {{ $plan->periodicity }}
                                            {{ __('fields.' . strtolower($periodType) . '') }}
                                        @endif
                                    </span>
                                </p>

                                {{-- Plan Description --}}
                                <p class="mt-4 text-gray-100 text-base/7 font-semibold text-start">
                                    {{ getPlanDescription($plan) }}
                                </p>
                                <button type="submit" aria-describedby="tier-{{ $plan->id }}"
                                    data-id="{{ $plan->id }}" @disabled(auth()->user()->company?->subscription?->plan_id == $plan->id)
                                    class="subscribe-btn w-full mt-8 block rounded-md disabled:bg-emerald-500 disabled:text-gray-200 bg-blue-600 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 sm:mt-10">
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
                            </th>
                        </form>
                    @endforeach
                </tr>
            </thead>
            <tbody class="{{ strtolower($periodType) }}ly-form hidden">
                @foreach ($features as $feature)
                    <tr @class(['bg-slate-50' => $loop->even])>
                        <td @class(['p-4 w-48 shadow-sm hover:shadow-lg transition-shadow duration-400', 'rounded-br-2xl' => $loop->last && session('locale') === 'ar', 'rounded-bl-2xl' => $loop->last && session('locale') === 'en'])>
                            <p @class([
                                'block text-base text-gray-700 font-bold',
                                'text-right' => app()->getLocale() === 'ar',
                            ])>
                                {{ getFeatureName($feature) }}
                            </p>
                        </td>
                        @foreach ($plans->where('periodicity_type', $periodType) as $plan)
                            <td @class([
                                'p-4 shadow-sm hover:shadow-lg transition-shadow duration-400',
                                'rounded-bl-2xl' => $loop->last && $loop->parent->last && session('locale') === 'ar',
                                'rounded-br-2xl' => $loop->last && $loop->parent->last && session('locale') === 'en',

                            ])>
                                @if ($plan->features->where('id', $feature->id)->isNotEmpty())
                                    @if (!$feature->consumable)
                                        <p class="flex justify-center">
                                            <svg class="h-7 w-7 text-green-500" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        </p>
                                    @else
                                        <p class="block text-sm text-gray-600 font-semibold text-center">
                                            <span class="text-xl">
                                                {{ (int) $plan->features->where('id', $feature->id)->first()->pivot->charges }}
                                            </span>
                                        </p>
                                    @endif
                                @else
                                    <p class="flex justify-center">
                                        <svg class="h-7 w-7 text-red-500" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" />
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                    </p>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            </div>
        @endforeach

    </table>


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
                .addClass('text-white bg-slate-800 hover:bg-slate-900')
                .removeClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.monthly-btn')
                .removeClass('text-white bg-slate-800 hover:bg-slate-900')
                .addClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.yearly-form').removeClass('hidden')
            $('.monthly-form').addClass('hidden')

        });

        $('.monthly-btn').on('click', function(e) {
            e.preventDefault();
            $(this)
                .addClass('text-white bg-slate-800 hover:bg-slate-900')
                .removeClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.annual-btn')
                .removeClass('text-white bg-slate-800 hover:bg-slate-900')
                .addClass('text-gray-900 bg-white hover:bg-gray-100 hover:text-blue-700');

            $('.yearly-form').addClass('hidden')
            $('.monthly-form').removeClass('hidden')
        });
    </script>
</body>

</html>
