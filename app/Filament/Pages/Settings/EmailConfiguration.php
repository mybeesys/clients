<?php

namespace App\Filament\Pages\Settings;

use App\SideBar;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Closure;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class EmailConfiguration extends BaseSettings
{
    use HasPageSidebar, SideBar;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;


    public function getTitle(): string
    {
        return __('main.email_settings');
    }

    public function schema(): array|Closure
    {
        return [
            TextInput::make('mail_host')
                ->label('Mail Host')
                ->placeholder('Mail Host')
                ->maxLength(255),
            TextInput::make('mail_port')
                ->label('Mail Port')
                ->placeholder('Mail Port')
                ->numeric(),
            TextInput::make('mail_username')
                ->label('Mail Username')
                ->placeholder('Mail Username')
                ->maxLength(255),
            TextInput::make('mail_password')
                ->label('Mail Password')
                ->password()
                ->placeholder('Mail Password')
                ->maxLength(255),
            TextInput::make('mail_from_address')
                ->label('Mail From Address')
                ->placeholder('Mail From Address')
                ->email()
                ->maxLength(255),
            TextInput::make('mail_from_name')
                ->label('Mail From Name')
                ->placeholder('Mail From Name')
                ->maxLength(255),
            TextInput::make('mail_encryption')
                ->label('Mail Encryption')
                ->placeholder('Mail Encryption')
                ->maxLength(255),
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
        $this->getSidebarItems();
    }
}
