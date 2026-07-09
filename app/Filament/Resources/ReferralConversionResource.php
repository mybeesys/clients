<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralConversionResource\Pages;
use App\Models\ReferralConversion;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralConversionResource extends Resource
{
    protected static ?string $model = ReferralConversion::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('referrals.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('referrals.conversions');
    }

    public static function getModelLabel(): string
    {
        return __('referrals.conversion');
    }

    public static function getPluralModelLabel(): string
    {
        return __('referrals.conversions_plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('referralCode.code')
                    ->label(__('referrals.fields.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('referralCode.employee_name')
                    ->label(__('referrals.fields.employee_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('referralCode.tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->badge(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('referrals.fields.company'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label(__('referrals.fields.plan')),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label(__('referrals.fields.points_awarded'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_distinct_device')
                    ->label(__('referrals.fields.distinct_device'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('referrals.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        'capped' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('referrals.statuses.'.$state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('referrals.fields.status'))
                    ->options([
                        'confirmed' => __('referrals.statuses.confirmed'),
                        'rejected' => __('referrals.statuses.rejected'),
                        'capped' => __('referrals.statuses.capped'),
                    ]),
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->options(fn () => ReferralConversion::query()
                        ->join('referral_codes', 'referral_codes.id', '=', 'referral_conversions.referral_code_id')
                        ->whereNotNull('referral_codes.tenant_id')
                        ->distinct()
                        ->pluck('referral_codes.tenant_id', 'referral_codes.tenant_id'))
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('referralCode', fn ($q) => $q->where('tenant_id', $data['value']));
                    }),
                Tables\Filters\TernaryFilter::make('is_distinct_device')
                    ->label(__('referrals.fields.distinct_device')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label(__('referrals.filters.from')),
                        \Filament\Forms\Components\DatePicker::make('until')->label(__('referrals.filters.until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralConversions::route('/'),
        ];
    }
}
