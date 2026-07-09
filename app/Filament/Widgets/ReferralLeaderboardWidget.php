<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ReferralCodeResource;
use App\Models\ReferralCode;
use App\Services\ReferralStatsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ReferralLeaderboardWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): ?string
    {
        return __('referrals.leaderboard_title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ReferralCode::query()
                    ->withCount([
                        'invitations',
                        'visits',
                        'conversions as confirmed_conversions_count' => fn ($query) => $query->where('status', 'confirmed'),
                    ])
                    ->orderByDesc('total_points')
                    ->orderByDesc('confirmed_conversions_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('employee_name')
                    ->label(__('referrals.fields.employee_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee_email')
                    ->label(__('referrals.fields.employee_email'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->badge(),
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
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('referrals.view_referrer'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (ReferralCode $record): string => ReferralCodeResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([10, 25, 50]);
    }
}
