<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use LucasDotVin\Soulbscription\Models\Subscription;
use PDF;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Company;
use Filament\Tables\Filters\Filter;


class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.subscriptions_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('main.subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.subscriptions');
    }


/*     public static function canCreate(): bool
    {
        return false;
    } */

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
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From'),
                        Forms\Components\DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['created_from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->label('Download PDF')
                    ->action(function (array $data, $livewire, $table) {
                        $records = $livewire->table($table)->getRecords();
                        $subscriptions = collect($records->items())->map(function ($item) {
                            $subscription = new Subscription();
                            $subscription->setRawAttributes($item->getAttributes(), true);
                            $subscription->exists = true;
                            return $subscription;
                        });

                  /*       $pdf = PDF::loadView('reports.subscription-report', ['subscriptions' => $subscriptions]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'subscription-report.pdf'); */
                    })
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
