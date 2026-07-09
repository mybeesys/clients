<?php

namespace App\Filament\Resources\ReferralCodeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('referrals.visits');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_distinct_device')
                    ->label(__('referrals.fields.distinct_device'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label(__('referrals.fields.ip_address')),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label(__('referrals.fields.user_agent'))
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([])
            ->bulkActions([]);
    }
}
