<x-filament-panels::page.simple>
    <div class="mybee-auth-topbar">
        <div class="mybee-auth-topbar__start">
            <img
                class="mybee-auth-topbar__logo"
                src="{{ asset('images/brand/mybee-lockup.png') }}"
                alt="My Bee"
            >
            <div class="mybee-auth-topbar__titles">
                <h1 class="mybee-auth-topbar__heading">
                    {{ __('main.wizard.login_heading') }}
                </h1>
                @if (filament()->hasRegistration())
                    <p class="mybee-auth-topbar__switch">
                        {{ __('filament-panels::pages/auth/login.actions.register.before') }}
                        {{ $this->registerAction }}
                    </p>
                @endif
            </div>
        </div>

        <div class="mybee-auth-topbar__brand">
            <span class="mybee-auth-topbar__brand-name">My Bee</span>
            <span class="mybee-auth-topbar__brand-tagline">{{ __('main.wizard.register_tagline') }}</span>
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
