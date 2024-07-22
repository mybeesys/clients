<?php

namespace Modules\Administration\Filament\Resources;

use Modules\Administration\Filament\Resources\PlanResource\Pages;
use Modules\Administration\Filament\Resources\PlanResource\RelationManagers;
use Filament\Forms;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LucasDotVin\Soulbscription\Models\Feature;
use Modules\Administration\Models\Plan;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Subscribtions';

    public static function form(Form $form): Form
    {
        return  $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\RichEditor::make('description')->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->prefix('$'),

                Forms\Components\TextInput::make('duration')
                    ->label('Duration (in days)')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),
                Forms\Components\Select::make('periodicity_type')
                    ->label('Periodicity type')
                    ->options([
                        PeriodicityType::Year => PeriodicityType::Year,
                        PeriodicityType::Month => PeriodicityType::Month,
                        PeriodicityType::Week => PeriodicityType::Week,
                        PeriodicityType::Day => PeriodicityType::Day,
                    ]),
                Forms\Components\Select::make('Featuers')
                    ->relationship(name: 'features', titleAttribute: 'name')
                    ->multiple()
                ->pivotData([
                    'value' => '3',
                ]),


            ])->columns(1);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->formatStateUsing(function ($state) {
                        return \Illuminate\Support\Str::limit(strip_tags($state), 50);
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price'),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state . ' days'),
                Tables\Columns\ToggleColumn::make('active')->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-user'),

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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
