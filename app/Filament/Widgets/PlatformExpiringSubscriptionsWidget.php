<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\SubscriptionResource;
use App\Models\Company;
use App\Models\Subscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PlatformExpiringSubscriptionsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getTableHeading(): ?string
    {
        return __('main.platform_dashboard.expiring_table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->with(['plan', 'company'])
                    ->where('subscriber_type', Company::class)
                    ->whereNull('suppressed_at')
                    ->whereNotNull('expired_at')
                    ->whereBetween('expired_at', [now(), now()->addDays(14)])
                    ->orderBy('expired_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('main.company'))
                    ->placeholder('—')
                    ->searchable()
                    ->url(fn (Subscription $record): ?string => $record->subscriber_id
                        ? CompanyResource::getUrl('edit', ['record' => $record->subscriber_id])
                        : null),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label(__('main.plan'))
                    ->placeholder(__('main.subscribe.custom_package'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label(__('main.subscribe.expires'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn (Subscription $record): string => $record->expired_at?->lte(now()->addDays(3)) ? 'danger' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label(__('general.view'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5])
            ->emptyStateHeading(__('main.platform_dashboard.expiring_empty'))
            ->emptyStateDescription(__('main.platform_dashboard.expiring_empty_hint'))
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
