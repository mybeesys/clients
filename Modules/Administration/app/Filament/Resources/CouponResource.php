<?php

namespace Modules\Administration\Filament\Resources;

use Modules\Administration\Filament\Resources\CouponResource\Pages;
use Modules\Administration\Filament\Resources\CouponResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Administration\Models\Coupon;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationGroup = 'Subscribtions';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


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
                        'Percentage' => 'Percentage',
                        'Fixed' => 'Fixed',
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
