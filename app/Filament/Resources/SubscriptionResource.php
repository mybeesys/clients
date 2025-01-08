<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
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


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('plan_id')
                            ->label(__('fields.plan'))
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('expired_at')->label(__('fields.end_date')),
                        DateTimePicker::make('started_at')->label(__('fields.start_date'))->required(),
                        DateTimePicker::make('grace_days_ended_at')->label(__('fields.grace_days_ended_at')),
                        MorphToSelect::make('subscriber')

                            ->label(__('fields.subscriber'))
                            ->types([
                                Type::make(Company::class)->titleAttribute('name')->label(__('fields.company')),
                            ])->preload()->searchable()->required(),
                        DateTimePicker::make('suppressed_at')->label(__('fields.suppressed_at')),
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('plan.name'),
                TextColumn::make('subscriber.name')->label('Company'),
                TextColumn::make('expired_at')->label('Expire at'),
                TextColumn::make('created_at')->label('Subscribed at'),
                TextColumn::make('plan.features')
                    ->label('Plan Features')
                    ->formatStateUsing(function ($state, $record) {
                        $features = $record->plan->features;
                        $featureList = $features->map(function ($feature) {
                            $name = app()->getLocale() === 'ar' ? 'name_ar' : 'name';
                            return "<li>&#128900; {$feature->{$name} } - {$feature->pivot->charges}</li>";
                        })->implode('');
                        return "<ul>{$featureList}</ul>";
                    })
                    ->html(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label(__('general.from')),
                        Forms\Components\DatePicker::make('created_to')->label(__('general.to')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['created_from'], fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_to'], fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
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
