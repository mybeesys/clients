<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Pages\Settings\HyperPayConfiguration;
use App\Filament\Resources\HyperPayPaymentResource\Pages;
use App\Models\HyperPayPayment;
use App\Services\HyperPay\HyperPayConfig;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HyperPayPaymentResource extends Resource
{
    protected static ?string $model = HyperPayPayment::class;

    protected static ?string $slug = 'hyperpay-payments';

    protected static ?int $navigationSort = 6;

    protected static bool $shouldSkipAuthorization = true;

    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getModelLabel(): string
    {
        return __('fields.hyperpay_payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('fields.hyperpay_payments');
    }

    public static function getNavigationLabel(): string
    {
        return __('fields.hyperpay_payments');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('status', HyperPayPayment::STATUS_PAID)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('fields.hyperpay_payment'))
                ->icon('heroicon-o-credit-card')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('status')
                        ->label(__('fields.status'))
                        ->badge()
                        ->formatStateUsing(fn ($state, HyperPayPayment $record) => $record->statusLabel())
                        ->color(fn ($state, HyperPayPayment $record) => $record->statusColor()),
                    Infolists\Components\TextEntry::make('amount')
                        ->label(__('fields.amount'))
                        ->formatStateUsing(fn ($state, HyperPayPayment $record) => $record->currency.' '.number_format((float) $state, 2)),
                    Infolists\Components\TextEntry::make('brand')
                        ->label(__('fields.hyperpay_brand'))
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('merchant_transaction_id')
                        ->label(__('fields.hyperpay_merchant_transaction_id'))
                        ->copyable(),
                    Infolists\Components\TextEntry::make('checkout_id')
                        ->label(__('fields.hyperpay_checkout_id'))
                        ->placeholder('—')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('payment_id')
                        ->label(__('fields.hyperpay_payment_id'))
                        ->placeholder('—')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('result_code')
                        ->label(__('fields.hyperpay_result_code'))
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('source')
                        ->label(__('fields.hyperpay_source'))
                        ->badge(),
                    Infolists\Components\TextEntry::make('result_description')
                        ->label(__('fields.hyperpay_result_description'))
                        ->columnSpanFull()
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('paid_at')
                        ->label(__('fields.paid_at'))
                        ->dateTime()
                        ->placeholder('—'),
                ]),
            Infolists\Components\Section::make(__('fields.company'))
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('company.name')
                        ->label(__('fields.company'))
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('plan.name')
                        ->label(__('fields.plan'))
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('billing_period')
                        ->label(__('general.period'))
                        ->formatStateUsing(fn (?string $state) => $state === 'Year' ? __('general.annual') : __('general.monthly')),
                    Infolists\Components\TextEntry::make('coupon_code')
                        ->label(__('fields.code'))
                        ->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('fields.company'))
                    ->searchable()
                    ->placeholder(__('main.wizard.payment_pending_company')),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('fields.amount'))
                    ->formatStateUsing(fn ($state, HyperPayPayment $record) => $record->currency.' '.number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('brand')
                    ->label(__('fields.hyperpay_brand'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('fields.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state, HyperPayPayment $record) => $record->statusLabel())
                    ->color(fn ($state, HyperPayPayment $record) => $record->statusColor()),
                Tables\Columns\TextColumn::make('source')
                    ->label(__('fields.hyperpay_source'))
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('fields.date'))
                    ->since()
                    ->tooltip(fn ($state) => $state?->timezone(config('app.timezone'))->format('Y-m-d H:i'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('fields.status'))
                    ->options([
                        HyperPayPayment::STATUS_PAID => __('fields.hyperpay_status_paid'),
                        HyperPayPayment::STATUS_FAILED => __('fields.hyperpay_status_failed'),
                        HyperPayPayment::STATUS_CHECKOUT => __('fields.hyperpay_status_checkout'),
                        HyperPayPayment::STATUS_PENDING => __('fields.hyperpay_status_pending'),
                        HyperPayPayment::STATUS_PENDING_GATEWAY => __('fields.hyperpay_status_pending_gateway'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('settings')
                    ->label(__('fields.hyperpay_open_settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(HyperPayConfiguration::getUrl()),
                Tables\Actions\Action::make('merchant_portal')
                    ->label(__('fields.hyperpay_merchant_portal'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn () => HyperPayConfig::instance()->merchantPortalUrl() ?: null)
                    ->visible(fn () => filled(HyperPayConfig::instance()->merchantPortalUrl()))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['company', 'plan', 'subscription']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHyperPayPayments::route('/'),
            'view' => Pages\ViewHyperPayPayment::route('/{record}'),
        ];
    }
}
