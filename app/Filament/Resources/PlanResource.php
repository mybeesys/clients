<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Feature;
use App\Models\Plan;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
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
                        Textarea::make('description')
                            ->string()
                            ->maxLength(255)
                            ->label(__('fields.description')),
                        Textarea::make('description_ar')
                            ->string()
                            ->maxLength(255)
                            ->label(__('fields.description_ar')),
                        TextInput::make('grace_days')
                            ->numeric()
                            ->default(0)
                            ->label(__('fields.grace_days'))
                            ->required(),
                        Toggle::make('active')->default(true)
                            ->label(__('fields.active')),
                        Section::make()->schema([
                            Select::make('discount_type')
                                ->label(__('general.discount_type'))
                                ->live()
                                ->options([
                                    'period' => __('general.period'),
                                    'value' => __('general.value'),
                                ]),
                            // repeater::make('specifications')
                            //     ->schema([
                            Section::make([
                                Cluster::make([
                                    Select::make('periodicity_type')
                                        ->live()
                                        ->distinct()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->required(fn(Get $get) => $get('discount_type') === 'period')
                                        ->placeholder(__('general.choose_duration'))
                                        ->options([
                                            PeriodicityType::Year => __('fields.year'),
                                            PeriodicityType::Month => __('fields.month'),
                                        ]),
                                    TextInput::make('periodicity')
                                        ->live(true)
                                        ->requiredWith('periodicity_type')
                                        ->required(fn(Get $get) => $get('discount_type') === 'period')
                                        ->placeholder(__('fields.count'))
                                        ->numeric()->afterStateUpdated(function (callable $set, $state) {
                                            self::calcPeriodDiscount($set, $state);
                                        }),
                                ])->label(__('fields.duration'))->afterStateUpdated(function (callable $set, $state) {
                                    self::calcPeriodDiscount($set, $state);
                                }),
                                Cluster::make([
                                    TextInput::make('price')
                                        ->required(fn(Get $get) => $get('discount_type') === 'period')
                                        ->numeric()
                                        ->suffix('SAR')
                                        ->live(true)
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->maxValue(999999)
                                ])->label(__('fields.price_before_dicount'))
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        self::calcPeriodDiscount($set, $state);
                                    }),
                                Cluster::make([
                                    TextInput::make('discount')
                                        ->label(__('fields.discount'))
                                        ->placeholder(__('fields.count'))
                                        ->required(fn(Get $get) => $get('discount_type') === 'period')
                                        ->live(true)
                                        ->numeric(),
                                    Select::make('discount_period_amount_type')
                                        ->required(fn(Get $get) => $get('discount_type') === 'period')
                                        ->live()
                                        ->placeholder(__('general.choose_duration'))
                                        ->options([
                                            PeriodicityType::Year => __('fields.year'),
                                            PeriodicityType::Month => __('fields.month'),
                                        ])
                                ])->label(__('fields.discount'))->afterStateUpdated(function (callable $set, $state) {
                                    self::calcPeriodDiscount($set, $state);
                                }),
                                TextInput::make('price_after_discount')
                                    ->requiredWith('periodicity_type')
                                    ->label(__('fields.price_after_dicount'))
                                    ->readOnly()
                                    ->numeric()
                                    ->suffix('SAR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->maxValue(999999),
                            ])->hidden(fn(Get $get) => $get('discount_type') !== 'period' ? true : false)->live(),
                            Section::make([
                                Cluster::make([
                                    TextInput::make('price')
                                        ->required(fn(Get $get) => $get('discount_type') === 'value')
                                        ->numeric()
                                        ->suffix('SAR')
                                        ->live(true)
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->maxValue(999999)
                                ])->label(__('fields.price_before_dicount'))
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        self::calcValueDiscount($set, $state);
                                    }),
                                Cluster::make([
                                    Select::make('discount_period_amount_type')
                                        ->required(fn(Get $get) => $get('discount_type') === 'value')
                                        ->live()
                                        ->options([
                                            'percent' => __('general.percent'),
                                            'fixed' => __('general.fixed'),
                                        ]),
                                    TextInput::make('discount')
                                        ->label(__('fields.discount'))
                                        ->placeholder(__('fields.count'))
                                        ->required(fn(Get $get) => $get('discount_type') === 'value')
                                        ->live(true)
                                        ->numeric(),
                                ])->label(__('fields.discount'))->afterStateUpdated(function (callable $set, $state) {
                                    self::calcValueDiscount($set, $state);
                                }),
                                TextInput::make('price_after_discount')
                                    ->requiredWith('periodicity_type')
                                    ->label(__('fields.price_after_dicount'))
                                    ->readOnly()
                                    ->numeric()
                                    ->suffix('SAR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->maxValue(999999),
                            ])->hidden(fn(Get $get) => $get('discount_type') !== 'value' ? true : false)->live(),
                            // ])
                            // ->minItems(1)
                            // ->maxItems(2)
                            // ->defaultItems(2)
                            // ->orderable(false)
                            // ->label(__('general.durations'))
                            // ->createItemButtonLabel(__('general.add_duration'))
                            // ->required(),
                        ])->columnSpan(1),
                        Section::make()->schema([
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
                                    TextInput::make('charges')
                                        ->label(__('fields.amount'))
                                        ->default(0)
                                        ->hidden(fn(Get $get) => !(Feature::find($get('feature_id'))?->consumable) ?? true)
                                        ->readOnly(fn(Get $get) => !(Feature::find($get('feature_id'))?->consumable) ?? true)
                                        ->live()
                                        ->numeric(),
                                ])
                                ->columns(2)
                                ->label(__('main.features'))
                                ->createItemButtonLabel(__('general.add_feature'))
                                ->required(),
                        ])->columnSpan(1),

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
                TextColumn::make('price_after_discount')
                    ->label(__('fields.price_after_dicount')),
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

    public static function calcPeriodDiscount(callable $set, $state)
    {
        if (is_array($state)) {
            if ($state['price'] && $state['discount'] && $state['discount_period_amount_type'] && $state['price'] && $state['periodicity_type']) {
                if ($state['periodicity_type'] === 'Month' && $state['discount_period_amount_type'] === 'Year') {
                    return $set('price_after_discount', 0);
                } else {
                    if ($state['periodicity_type'] === 'Year' && $state['discount_period_amount_type'] === 'Month') {
                        $period = $state['periodicity'] * 12;
                        $month_price = floatval(str_replace(',', '', $state['price'])) / $period;
                        $discount_amount = $state['discount'] * $month_price;
                        $new_price = floatval(str_replace(',', '', $state['price'])) - $discount_amount;
                        if ($new_price > 0) {
                            return $set('price_after_discount', round($new_price, 0));
                        } else {
                            return $set('price_after_discount', 0);
                        }
                    } elseif ($state['periodicity_type'] === 'Year' && $state['discount_period_amount_type'] === 'Year') {
                        $year_price = floatval(str_replace(',', '', $state['price'])) / $state['periodicity'];
                        $discount_amount = $state['discount'] * $year_price;
                        $new_price = floatval(str_replace(',', '', $state['price'])) - $discount_amount;
                        if ($new_price > 0) {
                            return $set('price_after_discount', round($new_price, 0));
                        } else {
                            return $set('price_after_discount', 0);
                        }
                    } elseif ($state['periodicity_type'] === 'Month' && $state['discount_period_amount_type'] === 'Month') {
                        $month_price = floatval(str_replace(',', '', $state['price'])) / $state['periodicity'];
                        $discount_amount = $state['discount'] * $month_price;
                        $new_price = floatval(str_replace(',', '', $state['price'])) - $discount_amount;
                        if ($new_price > 0) {
                            return $set('price_after_discount', round($new_price, 0));
                        } else {
                            return $set('price_after_discount', 0);
                        }
                    }
                }
            } else {
                return $set('price_after_discount', $state['price']);
            }
        }
    }

    public static function calcValueDiscount(callable $set, $state)
    {
        if (is_array($state)) {
            if ($state['price'] && $state['discount'] && $state['discount_period_amount_type'] && $state['price']) {
                if ($state['discount_period_amount_type'] === 'fixed') {
                    return $set('price_after_discount', (floatval(str_replace(',', '', $state['price'])) - $state['discount']) > 0 ? (floatval(str_replace(',', '', $state['price'])) - $state['discount']) : 0);
                } elseif ($state['discount_period_amount_type'] === 'percent') {
                    return $set('price_after_discount', floatval(str_replace(',', '', $state['price'])) - (floatval(str_replace(',', '', $state['price'])) * ($state['discount'] / 100)));
                } else {
                    return $set('price_after_discount', 0);
                }
            } else {
                return $set('price_after_discount', floatval(str_replace(',', '', $state['price'])));
            }
        }
    }
}
