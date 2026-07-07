<x-filament-panels::page
    @class([
        'fi-resource-manage-related-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament::section
            :heading="__('main.attach_company_member')"
            :description="__('main.attach_company_member_hint')"
            collapsible
            id="attach-company-member"
        >
            <x-filament-panels::form
                id="attach-member"
                :wire:key="$this->getId() . '.forms.attachMemberForm'"
                wire:submit.prevent="submitAttachMember"
            >
                {{ $this->attachMemberForm }}

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-filament::button
                        type="submit"
                        icon="heroicon-o-user-plus"
                        wire:loading.attr="disabled"
                        wire:target="submitAttachMember"
                    >
                        {{ __('main.attach_company_member_submit') }}
                    </x-filament::button>
                </div>
            </x-filament-panels::form>
        </x-filament::section>

        @if ($this->table->getColumns())
            <x-filament-panels::resources.tabs />

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_MANAGE_RELATED_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

            {{ $this->table }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_MANAGE_RELATED_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
        @endif
    </div>
</x-filament-panels::page>
