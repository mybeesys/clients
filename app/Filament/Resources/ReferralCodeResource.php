<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCodeResource\Pages;
use App\Filament\Resources\ReferralCodeResource\RelationManagers;
use App\Models\ReferralCode;
use App\Services\ReferralStatsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralCodeResource extends Resource
{
    protected static ?string $model = ReferralCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('referrals.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('referrals.codes');
    }

    public static function getModelLabel(): string
    {
        return __('referrals.code');
    }

    public static function getPluralModelLabel(): string
    {
        return __('referrals.codes');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('referrals.fields.code'))
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                Forms\Components\TextInput::make('employee_name')
                    ->label(__('referrals.fields.employee_name'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('employee_email')
                    ->label(__('referrals.fields.employee_email'))
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('employee_id')
                    ->label(__('referrals.fields.employee_id'))
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('referrals.fields.is_active'))
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'invitations',
                'visits',
                'conversions as confirmed_conversions_count' => fn ($q) => $q->where('status', 'confirmed'),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('referrals.fields.code'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('employee_name')
                    ->label(__('referrals.fields.employee_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_email')
                    ->label(__('referrals.fields.employee_email'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invitations_count')
                    ->label(__('referrals.stats.invitations'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('visits_count')
                    ->label(__('referrals.stats.visits'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('confirmed_conversions_count')
                    ->label(__('referrals.stats.confirmed_conversions'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_points')
                    ->label(__('referrals.fields.total_points'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('grade')
                    ->label(__('referrals.fields.grade'))
                    ->state(fn (ReferralCode $record): string => app(ReferralStatsService::class)->performanceScore($record))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'gray',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('referrals.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('total_points', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->options(fn () => ReferralCode::query()->whereNotNull('tenant_id')->distinct()->pluck('tenant_id', 'tenant_id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('referrals.fields.is_active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvitationsRelationManager::class,
            RelationManagers\VisitsRelationManager::class,
            RelationManagers\ConversionsRelationManager::class,
            RelationManagers\LedgerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralCodes::route('/'),
            'view' => Pages\ViewReferralCode::route('/{record}'),
            'edit' => Pages\EditReferralCode::route('/{record}/edit'),
        ];
    }
}
