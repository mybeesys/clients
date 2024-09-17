<?php

namespace App\Filament\Pages\Settings;

use App\SideBar;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Closure;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
class NotificationConfiguration extends BaseSettings
{
    use HasPageSidebar, SideBar;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('main.notifications_settings');
    }
    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->schema([
                            TextInput::make('general.brand_name')
                                ->required(),
                        ]),
                    Tabs\Tab::make('Seo')
                        ->schema([
                            TextInput::make('seo.title')
                                ->required(),
                            TextInput::make('seo.description')
                                ->required(),
                        ]),

                    Tabs\Tab::make('Contact us')
                        ->schema([
                            TextInput::make('contacts.phone')
                                ->required(),
                            TextInput::make('contacts.email')
                                ->required(),
                        ]),

                    Tabs\Tab::make('Social Media')
                        ->schema([
                            TextInput::make('media.facebook')
                                ->required(),
                            TextInput::make('media.instagram')
                                ->required(),
                            TextInput::make('media.linkedin')
                                ->required(),
                            TextInput::make('media.threads')
                                ->required(),
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
