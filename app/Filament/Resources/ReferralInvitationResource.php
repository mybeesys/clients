<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralInvitationResource\Pages;
use App\Models\ReferralInvitation;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralInvitationResource extends Resource
{
    protected static ?string $model = ReferralInvitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('referrals.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('referrals.invitations');
    }

    public static function getModelLabel(): string
    {
        return __('referrals.invitation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('referrals.invitations_plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('referralCode.employee_name')
                    ->label(__('referrals.fields.employee_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('referralCode.code')
                    ->label(__('referrals.fields.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('referralCode.tenant_id')
                    ->label(__('referrals.fields.tenant_id'))
                    ->badge(),
                Tables\Columns\TextColumn::make('channel')
                    ->label(__('referrals.fields.channel'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('referrals.channels.'.$state)),
                Tables\Columns\TextColumn::make('recipient_emails')
                    ->label(__('referrals.fields.recipients'))
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : '—')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->label(__('referrals.fields.channel'))
                    ->options([
                        'copy' => __('referrals.channels.copy'),
                        'email' => __('referrals.channels.email'),
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label(__('referrals.filters.from')),
                        \Filament\Forms\Components\DatePicker::make('until')->label(__('referrals.filters.until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralInvitations::route('/'),
        ];
    }
}
