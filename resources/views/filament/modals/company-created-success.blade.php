@php
    $companyName = $name ?? null;
    $tenantUrl = $url ?? null;
@endphp

<div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
    @if ($companyName)
        <p class="text-base text-gray-950 dark:text-white">
            {{ __('main.company_created.body', ['name' => $companyName]) }}
        </p>
    @endif

    @if ($tenantUrl)
        <p>{{ __('main.registration_thank_you.link_intro') }}</p>
        <div
            class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
            style="direction: ltr; text-align: left; word-break: break-all;"
        >
            {{ $tenantUrl }}
        </div>
        <x-filament::button
            tag="a"
            href="{{ $tenantUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            color="primary"
            class="w-full justify-center"
        >
            {{ __('main.company_created.open_system') }}
        </x-filament::button>
    @else
        <p class="text-gray-500">{{ __('main.registration_thank_you.no_link_yet') }}</p>
    @endif
</div>
