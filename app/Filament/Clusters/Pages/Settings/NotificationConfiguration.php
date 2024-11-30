<?php

namespace App\Filament\Clusters\Pages\Settings;


use App\Filament\Clusters\Settings;
use App\SideBar;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Closure;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class NotificationConfiguration extends BaseSettings
{
    protected static ?string $cluster = Settings::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?int $navigationSort = 6;
    
    public function getTitle(): string
    {
        return __('main.notifications_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.notifications_settings');
    }
    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->label(__('general.general'))
                        ->schema([
                            TextInput::make('general.brand_name')
                                ->label(__('general.brand_name'))
                                ->required(),
                        ]),
/*                     Tabs\Tab::make('Seo')
                        ->label(__('general.seo'))
                        ->schema([
                            TextInput::make('seo.title')
                                ->label(__('general.seo_title'))
                                ->required(),
                            TextInput::make('seo.description')
                                ->label(__('general.seo_description'))
                                ->required(),
                        ]), */

                    Tabs\Tab::make('Contact us')
                        ->label(__('general.contact_us'))
                        ->schema([
                            TextInput::make('contacts.phone')
                                ->label(__('fields.phone'))
                                ->required(),
                            TextInput::make('contacts.email')
                                ->label(__('fields.email'))
                                ->required(),
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
