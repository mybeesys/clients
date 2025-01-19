<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentSubscriptionResource\Pages;
use App\Models\PaymentSubscription;
use App\Models\Plan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentSubscriptionResource extends Resource
{
    protected static ?string $model = PaymentSubscription::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.Payments_subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('main.Payment_subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.Payments_subscriptions');
    }

    /*     public static function canCreate(): bool
        {
            return false;
        } */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('subscription_id')
                            ->label(__('fields.subscription'))
                            ->relationship('subscription', 'id')
                            ->exists('subscriptions', 'id')
                            ->required(),
                        Select::make('company_id')
                            ->label(__('fields.company'))
                            ->relationship('company', 'name')
                            ->exists('companies', 'id'),
                        Select::make('plan_id')
                            ->label(__('fields.plan'))
                            ->relationship('plan', 'name')
                            ->getOptionLabelFromRecordUsing(fn(Plan $record): ?string => (app()->getLocale() === 'ar' ? $record->name_ar : $record->name) . "- {$record->periodicity_type}")
                            ->exists('plans', 'id')
                            ->required(),
                        TextInput::make('amount')
                            ->label(__('fields.money_amount'))
                            ->numeric(),
                        DatePicker::make('payment_date')
                            ->label(__('fields.payment_date'))
                            ->date(),
                        TextInput::make('payment_method')
                            ->label(__('fields.payment_method'))
                            ->maxLength(255),
                        Select::make('status')
                            ->label(__('fields.status'))
                            ->options([
                                'paid' => __('general.paid'),
                                'not_paid' => __('general.not_paid'),
                            ])
                            ->in(['paid, not_paid']),
                        TextInput::make('transaction_id')
                            ->label(__('fields.trasaction_id'))
                            ->maxLength(255),
                        TextInput::make('remaining_amount')
                            ->label(__('fields.remaining_amount'))
                            ->numeric(),
                        TextInput::make('paid_amount')
                            ->label(__('fields.paid_amount'))
                            ->numeric(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscription_id')
                    ->label(__('fields.subscription'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('company_id')
                    ->label(__('fields.company')),
                TextColumn::make('plan_id')
                    ->label(__('fields.plan')),
                TextColumn::make('amount')
                    ->label(__('fields.money_amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label(__('fields.payment_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label(__('fields.payment_method'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('fields.status'))
                    ->badge()
                    ->colors([
                        'not_paid' => 'danger',
                        'paid' => 'success',
                    ]),
                TextColumn::make('transaction_id')
                    ->label(__('fields.trasaction_id'))
                    ->searchable(),
                TextColumn::make('remaining_amount')
                    ->label(__('fields.remaining_amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label(__('fields.paid_amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(__('fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentSubscriptions::route('/'),
            'create' => Pages\CreatePaymentSubscription::route('/create'),
            'edit' => Pages\EditPaymentSubscription::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
