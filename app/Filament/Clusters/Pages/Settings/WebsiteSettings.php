<?php

namespace App\Filament\Clusters\Pages\Settings;

use App\Filament\Clusters\Settings;
use App\SideBar;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class WebsiteSettings extends BaseSettings
{
    protected static ?string $cluster = Settings::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?int $navigationSort = 2;

    public function getTitle(): string
    {
        return __('main.web_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.web_settings');
    }
    public function schema(): array|Closure
    {
        return [
            Tabs::make('setting')
                ->schema([
                    Tabs\Tab::make('general')
                        ->label(__('general.general'))
                        ->schema([
                            TextInput::make('general.website_name')
                                ->label(__('general.website_name')),
                            TextInput::make('general.website_description')
                                ->label(__('general.website_description')),
                            FileUpload::make('website.logo')
                                ->label(__('general.website_logo'))
                                ->directory('uploads/logos')
                                ->storeFileNamesIn('image_path')
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    return (string) str($file->hashName());
                                })
                                ->image(),
                            FileUpload::make('website.favicon')
                                ->label(__('general.website_favicon'))
                                ->directory('uploads/favicons')
                                ->storeFileNamesIn('image_path')
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    return (string) str($file->hashName());
                                })
                                ->image(),
                        ]),
                    Tabs\Tab::make('others')
                        ->label(__('general.others'))
                        ->schema([
                            Toggle::make('website.maintenance_mode')
                                ->label(__('general.maintenance_mode'))
                                ->onIcon('heroicon-m-bolt')
                                ->offIcon('heroicon-m-user'),
                            RichEditor::make('website.our_services')
                                ->label(__('general.our_services')),
                            RichEditor::make('website.about_us')
                                ->label(__('general.about_us')),
                            RichEditor::make('website.who_are_we')
                                ->label(__('general.who_are_we')),
                        ])
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
