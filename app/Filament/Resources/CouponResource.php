<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


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
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\RichEditor::make('description')->columnSpan('full'),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(Coupon::class, 'code')
                    ->rule('regex:/^[a-zA-Z0-9._]+$/')
                    ->rule('regex:/^[a-zA-Z0-9]/')
                    ->maxLength(30),
                Forms\Components\Select::make('descount_type')
                    ->label('Descount Type')
                    ->options([
                        config('administration.coupons.types.percentage') => config('administration.coupons.types.percentage'),
                        config('administration.coupons.types.fixed') =>  config('administration.coupons.types.fixed'),
                    ])->required(),
                Forms\Components\TextInput::make('amount')->required()->numeric(),
                Forms\Components\TextInput::make('max_use')->numeric(),
                Forms\Components\DatePicker::make('expired_at')->required(),
                Forms\Components\Select::make('plans')
                    ->label('Plans')
                    ->multiple()
                    ->relationship('plans', 'name')
                    ->required(),
                Forms\Components\Toggle::make('active')->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('descount_type')
                    ->label('Descount Type'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('max_use'),
                Tables\Columns\ToggleColumn::make('active')->label('Active')->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Usage Count')
                    ->getStateUsing(fn (Coupon $record): int => $record->coupon_subscriptions()->count()),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
