<?php

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use App\Services\ReferralStatsService;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReferralCode extends ViewRecord
{
    protected static string $resource = ReferralCodeResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('referrals.referrer_profile'))
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('employee_name')
                            ->label(__('referrals.fields.employee_name')),
                        Infolists\Components\TextEntry::make('employee_email')
                            ->label(__('referrals.fields.employee_email')),
                        Infolists\Components\TextEntry::make('tenant_id')
                            ->label(__('referrals.fields.tenant_id'))
                            ->badge(),
                        Infolists\Components\TextEntry::make('code')
                            ->label(__('referrals.fields.code'))
                            ->copyable(),
                        Infolists\Components\TextEntry::make('invite_url')
                            ->label(__('referrals.fields.invite_url'))
                            ->state(fn ($record) => $record->inviteUrl())
                            ->copyable(),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label(__('referrals.fields.is_active'))
                            ->boolean(),
                        Infolists\Components\TextEntry::make('total_points')
                            ->label(__('referrals.fields.total_points'))
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('grade')
                            ->label(__('referrals.fields.grade'))
                            ->state(fn ($record) => app(ReferralStatsService::class)->performanceScore($record))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'A' => 'success',
                                'B' => 'info',
                                'C' => 'warning',
                                'D' => 'gray',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('invitations_count')
                            ->label(__('referrals.stats.invitations'))
                            ->state(fn ($record) => $record->invitations()->count()),
                        Infolists\Components\TextEntry::make('visits_count')
                            ->label(__('referrals.stats.visits'))
                            ->state(fn ($record) => $record->visits()->count()),
                        Infolists\Components\TextEntry::make('confirmed_conversions_count')
                            ->label(__('referrals.stats.confirmed_conversions'))
                            ->state(fn ($record) => $record->conversions()->where('status', 'confirmed')->count()),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('adjustPoints')
                ->label(__('referrals.adjust_points'))
                ->icon('heroicon-o-plus-circle')
                ->form([
                    Forms\Components\TextInput::make('points')
                        ->label(__('referrals.fields.points_delta'))
                        ->numeric()
                        ->required()
                        ->helperText(__('referrals.adjust_points_hint')),
                    Forms\Components\TextInput::make('reason')
                        ->label(__('referrals.fields.reason'))
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    app(ReferralStatsService::class)->adjustPoints(
                        $this->getRecord(),
                        (int) $data['points'],
                        $data['reason'],
                    );

                    Notification::make()
                        ->title(__('referrals.points_adjusted'))
                        ->success()
                        ->send();

                    $this->record->refresh();
                }),
        ];
    }
}
