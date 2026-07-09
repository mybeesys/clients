<?php

namespace App\Filament\Resources\ReferralCodeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConversionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('referrals.conversions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('referrals.fields.company')),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label(__('referrals.fields.plan')),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label(__('referrals.fields.points_awarded')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('referrals.statuses.'.$state)),
                Tables\Columns\IconColumn::make('is_distinct_device')
                    ->label(__('referrals.fields.distinct_device'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([])
            ->bulkActions([]);
    }
}
