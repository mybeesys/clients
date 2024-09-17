<?php

namespace App\Filament\Pages\Settings;

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
    use HasPageSidebar, SideBar;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('main.web_settings');
    }
    public function schema(): array|Closure
    {
        return [
            FileUpload::make('website.logo')
                ->label('Website Logo')
                ->directory('uploads/logos')
                ->storeFileNamesIn('image_path')
                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                    return (string) str($file->hashName());
                })
                ->image(),
            FileUpload::make('website.favicon')
                ->label('Website Favicon')
                ->directory('uploads/favicons')
                ->storeFileNamesIn('image_path')
                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                    return (string) str($file->hashName());
                })
                ->image(),
            RichEditor::make('website.our_services')
                ->label('Our Services'),
            RichEditor::make('website.about_us')
                ->label('About Us'),
            RichEditor::make('website.who_are_we')
                ->label('Who Are We'),
            Toggle::make('website.maintenance_mode')
                ->label('Maintenance Mode')
                ->onIcon('heroicon-m-bolt')
                ->offIcon('heroicon-m-user'),

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
