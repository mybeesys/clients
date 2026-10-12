<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use App\Support\TenantApplicationUrl;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PlatformRecentCompaniesWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): ?string
    {
        return __('main.platform_dashboard.recent_companies');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->with(['entitlement', 'user'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('main.company'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('fields.email'))
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('entitlement.period')
                    ->label(__('main.subscribe.period'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'Year' ? __('general.annual') : ($state ? __('general.monthly') : '—'))
                    ->color(fn (?string $state): string => $state === 'Year' ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('entitlement.period_total')
                    ->label(__('main.subscribe.price'))
                    ->formatStateUsing(fn ($state): string => $state === null ? '—' : number_format((float) $state, 0).' '.__('general.sar'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('general.view'))
                    ->icon('heroicon-m-building-office-2')
                    ->url(fn (Company $record): string => CompanyResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\Action::make('tenant')
                    ->label(__('fields.tenant_system_link'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Company $record): ?string => TenantApplicationUrl::forCompany($record), true)
                    ->visible(fn (Company $record): bool => filled(TenantApplicationUrl::forCompany($record))),
            ])
            ->paginated([8])
            ->emptyStateHeading(__('main.platform_dashboard.companies_empty'))
            ->emptyStateIcon('heroicon-o-building-office-2');
    }
}
