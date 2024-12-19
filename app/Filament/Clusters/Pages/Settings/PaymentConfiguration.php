<?php

namespace App\Filament\Clusters\Pages\Settings;


use App\Filament\Clusters\Settings;
use App\SideBar;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class PaymentConfiguration extends BaseSettings
{
    use HasPageShield;

    
    protected static ?string $cluster = Settings::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 4;

    public function getTitle(): string
    {
        return __('main.payment_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.payment_settings');
    }
    public function schema(): array|Closure
    {

        $payments = getAllPaymentsHelper();
        $sections = [];

        foreach ($payments as $payment) {
            $fields = [];

            foreach ($payment['fields'] as $field) {
                $fields[] = TextInput::make($field)
                    ->label($field)
                    ->placeholder($field);
            }

            $sections[] = Section::make($payment['name'])
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
}
