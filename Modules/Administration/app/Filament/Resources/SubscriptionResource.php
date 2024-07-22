<?php

namespace Modules\Administration\Filament\Resources;

use Modules\Administration\Filament\Resources\SubscriptionResource\Pages;
use Modules\Administration\Filament\Resources\SubscriptionResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Administration\Models\Subscription;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationGroup = 'Subscribtions';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name'),
                Tables\Columns\TextColumn::make('subscriber.name')->label('Company name'),
                Tables\Columns\TextColumn::make('expired_at')->label('Expires Date'),
                Tables\Columns\TextColumn::make('created_at')->label('Subscribe Date'),
                Tables\Columns\TextColumn::make('plan.features')
                    ->label('Features')
                    ->formatStateUsing(function ($state, $record) {
                        $features = $record->plan->features;
                        $featureList = $features->map(function ($feature) {
                            return "<li>&#128900; {$feature->name} - {$feature->pivot->charges}</li>";
                        })->implode('');
                        return "<ul>{$featureList}</ul>";
                    })
                    ->html(),

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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
