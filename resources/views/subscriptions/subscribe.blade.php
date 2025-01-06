@vite('resources/css/app.css')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>


    <div class="relative px-6 py-24 bg-white isolate sm:py-32 lg:px-8"
        dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="absolute inset-x-0 p-0 overflow-hidden -top-3 -z-10 transform-gpu px-36 blur-3xl" aria-hidden="true">
            <div class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30"
                style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)">
            </div>
        </div>
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="font-semibold text-indigo-600 text-base/7">@lang('general.pricing')</h2>
            <p class="mt-2 text-5xl font-semibold tracking-tight text-gray-900 text-balance sm:text-6xl">
                @lang('general.choose_right_plan')
            </p>
        </div>
        <p class="max-w-2xl mx-auto mt-6 text-lg font-medium text-center text-gray-600 text-pretty sm:text-xl/8">
            @lang('general.choose_plan_description')</p>
        <div
            class="flex flex-col items-center justify-center max-w-lg mx-auto mt-16 gap-y-6 sm:mt-20 lg:max-w-7xl lg:flex-row lg:flex-wrap lg:gap-x-5">

            @foreach ($plans as $plan)
                <div class="relative p-8 bg-gray-900 shadow-2xl rounded-3xl ring-1 ring-gray-900/10 sm:p-10">
                    <h3 id="tier-enterprise" class="font-semibold text-right text-indigo-400 text-base/7">
                        {{ (app()->getLocale() === 'ar' ? $plan->name_ar : $plan->name) ?? ($plan->name ?? $plan->name_ar) }}
                    </h3>
                    <p class="flex items-baseline mt-4 gap-x-2">
                        <span class="text-5xl font-semibold tracking-tight text-white">SAR {{ $plan->price }}</span>
                        <span class="text-base text-gray-400">/{{ $plan->periodicity }}
                            {{ $plan->periodicity_type }}</span>
                    </p>
                    <p class="mt-6 text-white text-base/7">
                        {{ (app()->getLocale() === 'ar' ? $plan->description_ar : $plan->description) ?? ($plan->description ?? $plan->description_ar) }}
                    </p>
                    <ul role="list" class="mt-8 space-y-3 text-gray-300 text-sm/6 sm:mt-10">
                        @foreach ($plan->features as $feature)
                            <li class="flex gap-x-3">
                                <svg class="flex-none w-5 h-6 text-indigo-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true" data-slot="icon">
                                    <path fill-rule="evenodd"
                                        d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ (app()->getLocale() === 'ar' ? $feature->name_ar : $feature->name) ?? ($feature->name ?? $feature->name_ar) }}
                                @if ($feature->countable)
                                    :{{ $feature->pivot->amount }}
                                @endif
                            </li>
                        @endforeach

                    </ul>
                    <a href="#" aria-describedby="tier-enterprise"
                        class="mt-8 block rounded-md bg-indigo-500 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 sm:mt-10">@lang('general.subscribe_now')</a>
                </div>
            @endforeach

        </div>
    </div>
</body>

</html>
