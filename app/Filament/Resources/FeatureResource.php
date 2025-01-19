<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureResource\Pages;
use App\Models\Feature;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;


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
 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')->label(__('fields.name')),
                Tables\Columns\TextColumn::make('name_ar')->label(__('fields.name_ar')),
            ])

            ->actions([
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
        ];
    }
}
