<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Plan;
use  Resources\PlanResource\RelationManagers\FeaturesRelationManager;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.plans');
    }

    public static function getModelLabel(): string
    {
        return __('main.plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.plans');
    }

    public static function form(Form $form): Form
    {
        return  $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\RichEditor::make('description')->required()->columnSpan('full'),
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
                Repeater::make('feature_plans')
                    ->relationship()
                    ->schema([
                        Select::make('feature_id')
                            ->searchable()
                            ->relationship('feature', 'name')->required(),

                        Forms\Components\TextInput::make('charges')
                            ->numeric()
                            ->required(),
                    ])->minItems(1)
                    ->required(),
            ]);
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
        return [];
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
