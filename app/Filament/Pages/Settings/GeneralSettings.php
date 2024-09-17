<?php

namespace App\Filament\Pages\Settings;

use App\SideBar;
use Closure;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class GeneralSettings extends BaseSettings
{
    use HasPageSidebar, SideBar;

    protected static ?string $navigationIcon = '';

    public static function getNavigationGroup(): ?string
    {
        return __('main.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.system_settings');
    }

    public static function getModelLabel(): string
    {
        return __('main.setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.settings');
    }

    public function getTitle(): string
    {
        return __('main.settings');
    }



    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->schema([
                            TextInput::make('general.website_name'),
                            TextInput::make('general.website_description'),

                        ]),


                    Tabs\Tab::make('Contact us')
                        ->schema([
                            TextInput::make('contacts.phone'),
                            TextInput::make('contacts.email'),
                        ]),

                    Tabs\Tab::make('Social Media')
                        ->schema([
                            TextInput::make('media.facebook'),
                            TextInput::make('media.instagram'),
                            TextInput::make('media.linkedin'),
                            TextInput::make('media.threads'),
                        ]),
                ]),
        ];
    }
    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('general.save'))
                ->submit('data')
                ->keyBindings(['mod+s'])
        ];
    }

    public function sidebar()
    {
        return $this->getSidebarItems();
    }


}
