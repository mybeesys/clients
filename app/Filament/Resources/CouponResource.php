<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.coupons_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.coupons');
    }

    public static function getModelLabel(): string
    {
        return __('main.coupon');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.coupons');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('fields.coupon_basics'))
                    ->description(__('fields.coupon_basics_help'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('fields.code'))
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label(__('fields.name'))
                            ->maxLength(255)
                            ->default(null),
                        DatePicker::make('start')
                            ->label(__('fields.start_date'))
                            ->required()
                            ->native(false)
                            ->default(now()->toDateString())
                            ->displayFormat('Y/m/d')
                            ->closeOnDateSelection()
                            ->live(),
                        DatePicker::make('end')
                            ->label(__('fields.end_date'))
                            ->required()
                            ->native(false)
                            ->default(now()->addYear()->toDateString())
                            ->displayFormat('Y/m/d')
                            ->closeOnDateSelection()
                            ->minDate(fn (Get $get): ?string => filled($get('start')) ? (string) $get('start') : null)
                            ->rule(fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                $start = $get('start');
                                if (filled($start) && filled($value) && (string) $value < (string) $start) {
                                    $fail(__('validation.after_or_equal', [
                                        'attribute' => __('fields.end_date'),
                                        'date' => __('fields.start_date'),
                                    ]));
                                }
                            }),
                        TextInput::make('uses_limit')
                            ->label(__('fields.uses_limit'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(__('fields.uses_limit_help')),
                        TextInput::make('uses_count')
                            ->label(__('fields.uses_count'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('value')
                            ->label(__('fields.value'))
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        ToggleButtons::make('type')
                            ->label(__('fields.type'))
                            ->options([
                                'percentage' => __('fields.percentage'),
                                'amount' => __('fields.amount'),
                            ])
                            ->in(['percentage', 'amount'])
                            ->inline()
                            ->icons([
                                'percentage' => 'heroicon-m-percent-badge',
                                'amount' => 'heroicon-m-currency-dollar',
                            ])
                            ->colors([
                                'percentage' => 'info',
                                'amount' => 'warning',
                            ])
                            ->required()
                            ->default('percentage'),
                        Select::make('plans')
                            ->label(__('main.plans'))
                            ->relationship('plans', 'name')
                            ->exists('plans', 'id')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Toggle::make('active')
                            ->label(__('fields.active'))
                            ->offColor('danger')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->default(true)
                            ->required(),
                    ]),

                Section::make(__('fields.coupon_customization'))
                    ->description(__('fields.coupon_customization_help'))
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('apply_mode')
                            ->label(__('fields.coupon_apply_mode'))
                            ->options([
                                'full' => __('fields.coupon_apply_full'),
                                'months' => __('fields.coupon_apply_months'),
                            ])
                            ->icons([
                                'full' => 'heroicon-m-receipt-percent',
                                'months' => 'heroicon-m-calendar-days',
                            ])
                            ->colors([
                                'full' => 'gray',
                                'months' => 'warning',
                            ])
                            ->inline()
                            ->required()
                            ->default('full')
                            ->live()
                            ->columnSpanFull()
                            ->helperText(__('fields.coupon_apply_mode_help')),
                        TextInput::make('apply_months')
                            ->label(__('fields.coupon_apply_months_count'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->suffix(__('fields.coupon_months_suffix'))
                            ->visible(fn (Get $get): bool => $get('apply_mode') === 'months')
                            ->required(fn (Get $get): bool => $get('apply_mode') === 'months')
                            ->dehydrated(fn (Get $get): bool => $get('apply_mode') === 'months')
                            ->helperText(__('fields.coupon_apply_months_help')),
                        TextInput::make('max_discount')
                            ->label(__('fields.coupon_max_discount'))
                            ->numeric()
                            ->minValue(0)
                            ->suffix(__('general.sar'))
                            ->helperText(__('fields.coupon_max_discount_help')),
                        Select::make('applicable_period')
                            ->label(__('fields.coupon_applicable_period'))
                            ->options([
                                'Month' => __('general.monthly'),
                                'Year' => __('general.annual'),
                            ])
                            ->placeholder(__('fields.coupon_period_any'))
                            ->native(false)
                            ->nullable()
                            ->helperText(__('fields.coupon_applicable_period_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('fields.code'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('start')
                    ->label(__('fields.start_date'))
                    ->badge()
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('end')
                    ->label(__('fields.end_date'))
                    ->badge()
                    ->color('danger')
                    ->date('Y/m/d')
                    ->sortable(),
                IconColumn::make('active')
                    ->label(__('fields.active'))
                    ->boolean(),
                TextColumn::make('apply_mode')
                    ->label(__('fields.coupon_apply_mode'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'months' => __('fields.coupon_apply_months'),
                        default => __('fields.coupon_apply_full'),
                    })
                    ->color(fn (?string $state): string => $state === 'months' ? 'warning' : 'gray')
                    ->toggleable(),
                TextColumn::make('apply_months')
                    ->label(__('fields.coupon_apply_months_count'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_discount')
                    ->label(__('fields.coupon_max_discount'))
                    ->placeholder('—')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('uses_limit')
                    ->label(__('fields.uses_limit'))
                    ->badge()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('uses_count')
                    ->label(__('fields.uses_count'))
                    ->badge()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('fields.type'))
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'percentage' => 'info',
                            'amount' => 'warning',
                            default => 'gray',
                        }
                    )
                    ->formatStateUsing(fn ($record) => __("fields.{$record->type}")),
                TextColumn::make('value')
                    ->label(__('fields.value'))
                    ->badge()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('fields.updated_at'))
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
