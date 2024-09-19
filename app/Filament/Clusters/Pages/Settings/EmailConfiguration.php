<?php

namespace App\Filament\Clusters\Pages\Settings;

use App\Filament\Clusters\Settings;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class EmailConfiguration extends BaseSettings
{
    protected static ?string $cluster = Settings::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?int $navigationSort = 5;

    public function getTitle(): string
    {
        return __('main.email_settings');
    }

    public static function getNavigationLabel(): string
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
}
