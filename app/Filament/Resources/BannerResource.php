<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;


class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.banners');
    }

    public static function getModelLabel(): string
    {
        return __('main.banner');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.banners');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('fields.name'))
                            ->maxLength(255),
                        TextInput::make('description')
                            ->label(__('fields.description'))
                            ->maxLength(255),
                        FileUpload::make('image')
                            ->label(__('fields.image'))
                            ->directory('banners')
                            ->image()
                            ->required(),
                        Toggle::make('active')
                            ->label(__('fields.active'))
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-user'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('fields.description')),
                ImageColumn::make('image')
                    ->label(__('fields.image'))
                    ->circular(),
                ToggleColumn::make('active')
                    ->label(__('fields.active'))->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
