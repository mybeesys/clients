<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Feature;
use App\Models\Plan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Guava\FilamentClusters\Forms\Cluster;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.plans');
    }

    public static function getModelLabel(): string
    {
        return __('main.plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.plans');
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
                            ->required(),
                        TextInput::make('name_ar')
                            ->label(__('fields.name_ar'))
                            ->required(),
                        TextInput::make('price')
                            ->label(__('fields.price'))
                            ->numeric()
                            ->required()
                            ->prefix('SAR')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->maxValue(999999),
                        Cluster::make([
                            TextInput::make('periodicity')
                                ->placeholder(__('fields.count'))
                                ->disabled(fn(Get $get) => $get('periodicity_type') ? false : true)
                                ->numeric()
                                ->required(),
                            Select::make('periodicity_type')
                                ->required()
                                ->live()
                                ->placeholder(__('general.choose_duration'))
                                ->options([
                                    PeriodicityType::Year => __('fields.year'),
                                    PeriodicityType::Month => __('fields.month'),
                                    PeriodicityType::Week => __('fields.weak'),
                                    PeriodicityType::Day => __('fields.day'),
                                ]),
                        ])->label(__('fields.duration')),
                        Toggle::make('active')
                            ->label(__('fields.active')),
                        Textarea::make('description')
                            ->string()
                            ->maxLength(255)
                            ->label(__('fields.description'))
                            ->columnSpanFull(),
                        Textarea::make('description_ar')
                            ->string()
                            ->maxLength(255)
                            ->label(__('fields.description_ar'))
                            ->columnSpanFull(),
                        Repeater::make('features')
                            ->relationship('feature_plans')
                            ->schema([
                                Select::make('feature_id')
                                    ->label(__('main.feature'))
                                    ->options(Feature::all()->pluck('translatedName', 'id')->toArray())
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(fn(callable $set, $state) => $set('amount', null))
                                    ->exists('features', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('amount')
                                    ->label(__('fields.amount'))
                                    ->disabled(fn(Get $get) => !(Feature::find($get('feature_id'))?->countable) ?? true)
                                    ->live()
                                    ->numeric(),
                            ])
                            ->columns(2)
                            ->label(__('main.features'))
                            ->createItemButtonLabel(__('general.add_feature'))
                            ->required()

                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('fields.name')),
                TextColumn::make('name_ar')
                    ->label(__('fields.name_ar')),
                TextColumn::make('description')
                    ->label(__('fields.description'))
                    ->formatStateUsing(function ($state) {
                        return \Illuminate\Support\Str::limit(strip_tags($state), 50);
                    }),
                TextColumn::make('description_ar')
                    ->label(__('fields.description_ar'))
                    ->formatStateUsing(function ($state) {
                        return \Illuminate\Support\Str::limit(strip_tags($state), 50);
                    }),
                TextColumn::make('price')
                    ->label(__('fields.price')),
                TextColumn::make('periodicity')
                    ->label(__('fields.duration')),
                TextColumn::make('periodicity_type')
                    ->label(__('fields.duration'))
                    ->formatStateUsing(function ($record) {
                        return match ($record->periodicity_type) {
                            'Year' => __('fields.year'),
                            'Month' => __('fields.month'),
                            'Weak' => __('fields.weak'),
                            'Day' => __('fields.day'),
                        };
                    }),
                ToggleColumn::make('active')
                    ->label(__('fields.active'))
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
