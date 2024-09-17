<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentSubscriptionResource\Pages;

use App\Models\PaymentSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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

    public static function canCreate(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->label('Payment Id'),
                Forms\Components\Select::make('Plan')
                    ->relationship(name: 'plan', titleAttribute: 'name'),
                Forms\Components\Select::make('Subscription')
                    ->relationship(name: 'subscription', titleAttribute: 'id')
                    ->label('Subscription Id'),
                Forms\Components\Select::make('company_id')
                    ->label('Company')
                    ->relationship('company.user', 'name'),
                Forms\Components\TextInput::make('paid_amount'),
                Forms\Components\TextInput::make('remaining_amount'),
                Forms\Components\TextInput::make('payment_method'),
                Forms\Components\TextInput::make('payment_date'),
                Forms\Components\Select::make('status')
                    ->options([
                        config('administration.subscription_payments.status.paid') =>  config('administration.subscription_payments.status.paid'),
                        config('administration.subscription_payments.status.not_paid') =>  config('administration.subscription_payments.status.not_paid'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('company.name')->label('Company Name'),
                Tables\Columns\TextColumn::make('plan.name')->label('Plan Name'),
                Tables\Columns\TextColumn::make('subscription.id')->label('Subscription Id'),
                Tables\Columns\TextColumn::make('amount'),



            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPaymentSubscriptions::route('/'),
            'create' => Pages\CreatePaymentSubscription::route('/create'),
            'edit' => Pages\EditPaymentSubscription::route('/{record}/edit'),
        ];
    }
}
