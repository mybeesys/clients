<?php

namespace App\Filament\Clusters\Pages\Settings;


use App\Filament\Clusters\Settings;
use App\SideBar;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Closure;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class GeneralSettings extends BaseSettings
{
    use HasPageShield;

    protected static ?string $cluster = Settings::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('main.system_settings');
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
                        ->label(__('general.general'))
                        ->schema([
                            TextInput::make('general.website_name')
                                ->label(__('general.website_name')),
                            TextInput::make('general.website_description')
                                ->label(__('general.website_description')),
                        ]),
                        
                    Tabs\Tab::make('Contact us')
                        ->label(__('general.contact_us'))
                        ->schema([
                            TextInput::make('contacts.phone')
                                ->label(__('fields.phone')),
                            TextInput::make('contacts.email')
                                ->label(__('fields.email')),
                        ]),

                    Tabs\Tab::make('Social Media')
                        ->label(__('general.social_media'))
                        ->schema([
                            TextInput::make('media.facebook')
                                ->label(__('general.facebook')),
                            TextInput::make('media.instagram')
                                ->label(__('general.instagram')),
                            TextInput::make('media.linkedin')
                                ->label(__('general.linkedin')),
                            TextInput::make('media.threads')
                                ->label(__('general.threads')),
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

}
