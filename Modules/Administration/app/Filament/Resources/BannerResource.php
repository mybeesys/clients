<?php

namespace Modules\Administration\Filament\Resources;

use Modules\Administration\Filament\Resources\BannerResource\Pages;
use Modules\Administration\Filament\Resources\BannerResource\RelationManagers;
use Modules\Administration\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;
    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->directory('banners')
                    ->image()
                    ->required(),
                Forms\Components\Toggle::make('active')->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Active')->onIcon('heroicon-m-bolt')
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
