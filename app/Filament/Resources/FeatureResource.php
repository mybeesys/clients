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
    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make()
    //                 ->columns(1)
    //                 ->columnSpan(1)
    //                 ->schema([
    //                     TextInput::make('name')->required()
    //                         ->label(__('fields.name')),
    //                     Textarea::make('description')
    //                         ->label(__('fields.description')),
    //                     Cluster::make([
    //                         TextInput::make('periodicity')
    //                             ->placeholder(__('fields.count'))
    //                             ->requiredWith('periodicity_type')
    //                             ->disabled(fn(Get $get) => $get('periodicity_type') ? false : true)
    //                             ->numeric(),
    //                         Select::make('periodicity_type')
    //                             ->placeholder(__('general.choose_duration'))
    //                             ->requiredWith('periodicity')
    //                             ->live()
    //                             ->options([
    //                                 PeriodicityType::Year => __('fields.year'),
    //                                 PeriodicityType::Month => __('fields.month'),
    //                                 PeriodicityType::Week => __('fields.weak'),
    //                                 PeriodicityType::Day => __('fields.day'),
    //                             ]),
    //                     ])->label(__('fields.duration') . ' (' . __('general.optional') . ')'),
    //                     Toggle::make('consumable')
    //                         ->label(__('fields.active')),
    //                 ])
    //         ]);
    // }

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
