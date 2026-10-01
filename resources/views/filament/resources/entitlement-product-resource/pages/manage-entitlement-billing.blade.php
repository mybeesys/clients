<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">
                {{ __('general.save') }}
            </x-filament::button>

            <x-filament::button
                color="gray"
                tag="a"
                :href="\App\Filament\Resources\EntitlementProductResource::getUrl('index')"
                icon="heroicon-o-arrow-left"
            >
                {{ __('main.entitlement_back_to_catalog') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
