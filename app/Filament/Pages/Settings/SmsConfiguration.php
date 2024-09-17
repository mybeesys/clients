<?php

namespace App\Filament\Pages\Settings;

use App\SideBar;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Closure;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class SmsConfiguration extends BaseSettings
{
    use HasPageSidebar, SideBar;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('main.SMS_settings');
    }
    public function schema(): array|Closure
    {
        $otps = getAllSMSHelper();
        $sections = [];

        foreach ($otps as $otp) {
            $fields = [];

            foreach ($otp['fields'] as $field) {
                $fields[] = TextInput::make($field)
                    ->label($field)
                    ->placeholder($field);
            }

            $sections[] = Section::make($otp['name'])
                ->schema($fields);
        }

        return $sections;
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
