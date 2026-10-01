<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntitlementProductResource\Pages;
use App\Models\EntitlementProduct;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EntitlementProductResource extends Resource
{
    protected static ?string $model = EntitlementProduct::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.entitlement_catalog');
    }

    public static function getModelLabel(): string
    {
        return __('main.entitlement_product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.entitlement_catalog');
    }

    /**
     * @return array<string, string>
     */
    public static function moduleOptions(?string $exceptKey = null): array
    {
        return EntitlementProduct::query()
            ->where('type', EntitlementProduct::TYPE_MODULE)
            ->when($exceptKey, fn ($q) => $q->where('key', '!=', $exceptKey))
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (EntitlementProduct $p) => [
                $p->key => ($p->name_ar ?: $p->name_en).' ('.$p->key.')',
            ])
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('main.entitlement_section_identity'))
                    ->schema([
                        TextInput::make('key')
                            ->label(__('fields.code'))
                            ->disabled()
                            ->dehydrated(),
                        Select::make('type')
                            ->label(__('fields.type'))
                            ->options([
                                EntitlementProduct::TYPE_PLATFORM => __('main.entitlement_type_platform'),
                                EntitlementProduct::TYPE_MODULE => __('main.entitlement_type_module'),
                                EntitlementProduct::TYPE_QUOTA => __('main.entitlement_type_quota'),
                            ])
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('name_en')
                            ->label(__('fields.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label(__('fields.name_ar'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description_en')
                            ->label(__('fields.description'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('description_ar')
                            ->label(__('fields.description_ar'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('main.entitlement_section_pricing'))
                    ->schema([
                        TextInput::make('price_month')
                            ->label(__('main.entitlement_price_month'))
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->prefix(__('general.sar'))
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                EntitlementProduct::TYPE_PLATFORM,
                                EntitlementProduct::TYPE_MODULE,
                            ], true)),
                        TextInput::make('price_per_extra_month')
                            ->label(__('main.entitlement_price_per_extra'))
                            ->numeric()
                            ->minValue(0)
                            ->prefix(__('general.sar'))
                            ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_QUOTA),
                        TextInput::make('included')
                            ->label(__('main.entitlement_included'))
                            ->numeric()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_QUOTA),
                        TextInput::make('min')
                            ->label(__('main.entitlement_min'))
                            ->numeric()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_QUOTA),
                        TextInput::make('max')
                            ->label(__('main.entitlement_max'))
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_QUOTA),
                        TextInput::make('linked_module')
                            ->label(__('main.entitlement_linked_module'))
                            ->helperText(__('main.entitlement_linked_module_hint'))
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_QUOTA && filled($get('linked_module'))),
                        TextInput::make('sort_order')
                            ->label(__('main.entitlement_sort_order'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('active')
                            ->label(__('fields.active'))
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make(__('main.entitlement_section_dependencies'))
                    ->description(__('main.entitlement_section_dependencies_hint'))
                    ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_MODULE)
                    ->schema([
                        Select::make('requires')
                            ->label(__('main.entitlement_requires'))
                            ->helperText(__('main.entitlement_requires_hint'))
                            ->multiple()
                            ->searchable()
                            ->options(fn (?Model $record) => static::moduleOptions($record?->key))
                            ->columnSpanFull(),
                        Select::make('meta.requires_any')
                            ->label(__('main.entitlement_requires_any'))
                            ->helperText(__('main.entitlement_requires_any_hint'))
                            ->multiple()
                            ->searchable()
                            ->options(fn (?Model $record) => static::moduleOptions($record?->key))
                            ->columnSpanFull(),
                        Select::make('meta.grants')
                            ->label(__('main.entitlement_grants'))
                            ->helperText(__('main.entitlement_grants_hint'))
                            ->multiple()
                            ->searchable()
                            ->options(fn (?Model $record) => static::moduleOptions($record?->key)
                                + collect(config('entitlements.legacy_modules', []))
                                    ->mapWithKeys(fn ($k) => [$k => $k.' (legacy)'])
                                    ->all()
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make(__('main.entitlement_section_includes'))
                    ->description(__('main.entitlement_section_includes_hint'))
                    ->visible(fn (Get $get): bool => $get('type') === EntitlementProduct::TYPE_MODULE)
                    ->schema([
                        TagsInput::make('meta.includes.ar')
                            ->label(__('main.entitlement_includes_ar'))
                            ->placeholder(__('main.entitlement_includes_placeholder'))
                            ->suggestions([])
                            ->columnSpanFull(),
                        TagsInput::make('meta.includes.en')
                            ->label(__('main.entitlement_includes_en'))
                            ->placeholder(__('main.entitlement_includes_placeholder'))
                            ->suggestions([])
                            ->columnSpanFull(),
                        Hidden::make('meta.menu_keys'),
                        Hidden::make('meta.api_prefixes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('fields.code'))
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        EntitlementProduct::TYPE_PLATFORM => __('main.entitlement_type_platform'),
                        EntitlementProduct::TYPE_MODULE => __('main.entitlement_type_module'),
                        EntitlementProduct::TYPE_QUOTA => __('main.entitlement_type_quota'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        EntitlementProduct::TYPE_PLATFORM => 'success',
                        EntitlementProduct::TYPE_MODULE => 'info',
                        EntitlementProduct::TYPE_QUOTA => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('fields.name_ar'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('fields.name'))
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('requires')
                    ->label(__('main.entitlement_requires'))
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('meta.grants')
                    ->label(__('main.entitlement_grants'))
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price_month')
                    ->label(__('main.entitlement_price_month'))
                    ->formatStateUsing(function ($state, EntitlementProduct $record): string {
                        if ($record->type === EntitlementProduct::TYPE_QUOTA) {
                            return number_format((float) $record->price_per_extra_month, 0)
                                .' '.__('general.sar').' / +1';
                        }

                        return number_format((float) $state, 0).' '.__('general.sar');
                    }),
                Tables\Columns\TextColumn::make('included')
                    ->label(__('main.entitlement_included'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('linked_module')
                    ->label(__('main.entitlement_linked_module'))
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('active')
                    ->label(__('fields.active')),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        EntitlementProduct::TYPE_PLATFORM => __('main.entitlement_type_platform'),
                        EntitlementProduct::TYPE_MODULE => __('main.entitlement_type_module'),
                        EntitlementProduct::TYPE_QUOTA => __('main.entitlement_type_quota'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * Schema for managing "What type of system?" recommendations.
     */
    public static function businessTypesFormSchema(): array
    {
        return [
            Repeater::make('recommendations')
                ->label(__('main.entitlement_business_types'))
                ->helperText(__('main.entitlement_business_types_hint'))
                ->reorderable()
                ->collapsible()
                ->cloneable()
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => filled($state['name_ar'] ?? null)
                    ? (($state['active'] ?? true) ? '' : '⏸ ').($state['name_ar'] ?? $state['name_en'] ?? $state['key'] ?? null)
                    : ($state['key'] ?? __('main.entitlement_business_type_new')))
                ->addActionLabel(__('main.entitlement_business_type_add'))
                ->schema([
                    TextInput::make('key')
                        ->label(__('fields.code'))
                        ->required()
                        ->maxLength(64)
                        ->alphaDash()
                        ->helperText(__('main.entitlement_business_type_key_hint')),
                    TextInput::make('sort_order')
                        ->label(__('main.entitlement_sort_order'))
                        ->numeric()
                        ->default(10),
                    Toggle::make('active')
                        ->label(__('fields.active'))
                        ->default(true)
                        ->inline(false),
                    TextInput::make('name_ar')
                        ->label(__('fields.name_ar'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),
                    TextInput::make('name_en')
                        ->label(__('fields.name'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description_ar')
                        ->label(__('fields.description_ar'))
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('description_en')
                        ->label(__('fields.description'))
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('modules')
                        ->label(__('main.entitlement_business_type_modules'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(fn () => static::moduleOptions())
                        ->columnSpanFull(),
                    TextInput::make('quotas.employees')
                        ->label(__('main.entitlement_quota_employees_default'))
                        ->numeric()
                        ->minValue(1)
                        ->default(5),
                    TextInput::make('quotas.establishments')
                        ->label(__('main.entitlement_quota_branches_default'))
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    TextInput::make('quotas.screen_devices')
                        ->label(__('main.entitlement_quota_screens_default'))
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                ])
                ->columns(3)
                ->defaultItems(0)
                ->columnSpanFull(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEntitlementProducts::route('/'),
            'business-types' => Pages\ManageBusinessTypes::route('/business-types'),
            'billing' => Pages\ManageEntitlementBilling::route('/billing'),
            'edit' => Pages\EditEntitlementProduct::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
