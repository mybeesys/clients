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
use App\Models\Company;
use Filament\Tables\Actions\Action;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationGroup = 'Subscribtions';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';


    public static function canCreate(): bool
    {
        return false;
    }




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_id')
                    ->label('Plan Name')
                    ->relationship('Plan', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('subscriber_id')
                    ->label('Company')
                    ->options(function () {
                        $companyIds = Subscription::where('subscriber_type', 'App\Models\Company')
                            ->pluck('subscriber_id');
                        return Company::whereIn('id', $companyIds)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('expired_at')->label('Expires Date'),
                Forms\Components\TextInput::make('created_at')->label('Subscribe Date'),
                Forms\Components\TextInput::make('subdomain')
                    ->label('Company Subdomain'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('plan.name'),
                Tables\Columns\TextColumn::make('subscriber.name')->label('Company'),
                Tables\Columns\TextColumn::make('expired_at')->label('Expire at'),
                Tables\Columns\TextColumn::make('created_at')->label('Subscribed at'),
                Tables\Columns\TextColumn::make('plan.features')
                    ->label('Plan Features')
                    ->formatStateUsing(function ($state, $record) {
                        $features = $record->plan->features;
                        $featureList = $features->map(function ($feature) {
                            return "<li>&#128900; {$feature->name} - {$feature->pivot->charges}</li>";
                        })->implode('');
                        return "<ul>{$featureList}</ul>";
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('subdomain')
                    ->label('Subdomain')
                    ->url(fn ($record) => 'https://' . $record->subdomain)
                    ->openUrlInNewTab()
                    ->html(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('Download pdf')
                    ->icon('heroicon-o-squares-2x2')
                    ->url(route('subscriptions.pdf.download'))->openUrlInNewTab(),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),

        ];
    }
}
