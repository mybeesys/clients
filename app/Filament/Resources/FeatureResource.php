<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LucasDotVin\Soulbscription\Models\Feature;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;
    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.features');
    }

    public static function getModelLabel(): string
    {
        return __('main.feature');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.features');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\RichEditor::make('description')->required(),
                Forms\Components\Toggle::make('consumable')->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description')->html(),
                Tables\Columns\ToggleColumn::make('consumable')->label('Active')->onIcon('heroicon-m-bolt')
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
            ]);
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
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
