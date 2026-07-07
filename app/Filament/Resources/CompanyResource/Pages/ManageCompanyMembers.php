<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\User;
use App\Services\CompanyMembershipService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ManageCompanyMembers extends ManageRelatedRecords
{
    protected static string $resource = CompanyResource::class;

    protected static string $relationship = 'members';

    protected static string $view = 'filament.resources.company-resource.pages.manage-company-members';

    /**
     * @var array<string, mixed>
     */
    public array $attachMemberData = [
        'role' => 'member',
        'is_primary' => false,
    ];

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->attachMemberForm->fill([
            'role' => 'member',
            'is_primary' => false,
        ]);
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function getTitle(): string | Htmlable
    {
        return __('main.company_members');
    }

    public function getSubheading(): string | Htmlable | null
    {
        return $this->getRecord()->name;
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            CompanyResource::getUrl() => CompanyResource::getPluralModelLabel(),
            CompanyResource::getUrl('edit', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            '#' => __('main.company_members'),
        ];
    }

    /**
     * @return array<string, Form>
     */
    protected function getForms(): array
    {
        return [
            'attachMemberForm',
        ];
    }

    public function attachMemberForm(Form $form): Form
    {
        return $form
            ->schema($this->attachMemberFormSchema())
            ->statePath('attachMemberData')
            ->columns(2);
    }

    public function submitAttachMember(): void
    {
        $data = $this->attachMemberForm->getState();
        $company = $this->getOwnerRecord();
        $userId = (int) ($data['user_id'] ?? 0);
        $user = User::query()->findOrFail($userId);

        if ($company->members()->whereKey($userId)->exists()) {
            Notification::make()
                ->title(__('main.company_member_already_attached'))
                ->warning()
                ->send();

            return;
        }

        try {
            app(CompanyMembershipService::class)->attachMember(
                $user,
                $company,
                $data['role'] ?? 'member',
                (bool) ($data['is_primary'] ?? false),
            );
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('general.error_occurred'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->attachMemberForm->fill([
            'user_id' => null,
            'role' => 'member',
            'is_primary' => false,
        ]);

        Notification::make()
            ->title(__('main.company_member_attached'))
            ->success()
            ->send();
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected function attachMemberFormSchema(): array
    {
        return [
            Forms\Components\Select::make('user_id')
                ->label(__('fields.user'))
                ->placeholder(__('main.select_company_member'))
                ->searchable()
                ->preload()
                ->native(false)
                ->options(fn (): array => $this->availableUserOptions())
                ->noSearchResultsMessage(__('main.no_users_available_to_attach'))
                ->dehydrateStateUsing(fn ($state): ?int => filled($state) ? (int) $state : null)
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('role')
                ->label(__('fields.role'))
                ->options([
                    'owner' => __('main.company_member_roles.owner'),
                    'admin' => __('main.company_member_roles.admin'),
                    'member' => __('main.company_member_roles.member'),
                ])
                ->default('member')
                ->required(),
            Forms\Components\Toggle::make('is_primary')
                ->label(__('main.company_member_primary'))
                ->helperText(__('main.company_member_primary_hint')),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function availableUserOptions(?string $search = null): array
    {
        $company = $this->getOwnerRecord();
        $attachedIds = $company->members()->pluck('users.id');

        $query = User::query()
            ->whereNotIn('id', $attachedIds)
            ->orderBy('email');

        if (filled($search)) {
            $query->where(function (Builder $inner) use ($search): void {
                $inner->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                (string) $user->id => $user->email.' — '.$user->name,
            ])
            ->all();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('role')
                ->label(__('fields.role'))
                ->options([
                    'owner' => __('main.company_member_roles.owner'),
                    'admin' => __('main.company_member_roles.admin'),
                    'member' => __('main.company_member_roles.member'),
                ])
                ->default('member')
                ->required(),
            Forms\Components\Toggle::make('is_primary')
                ->label(__('main.company_member_primary'))
                ->helperText(__('main.company_member_primary_hint')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('fields.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('fields.role'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'owner' => __('main.company_member_roles.owner'),
                        'admin' => __('main.company_member_roles.admin'),
                        default => __('main.company_member_roles.member'),
                    }),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label(__('main.company_member_primary'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('syncOwner')
                    ->label(__('main.sync_company_owner_member'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (): bool => filled($this->getOwnerRecord()->user_id))
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $company = $this->getOwnerRecord();
                        $owner = $company->user;

                        if ($owner === null) {
                            return;
                        }

                        app(CompanyMembershipService::class)->attachOwner($owner, $company);

                        Notification::make()
                            ->title(__('main.company_owner_member_synced'))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading(__('main.edit_company_member'))
                    ->modalSubmitActionLabel(__('general.save'))
                    ->using(function (User $record, array $data): void {
                        app(CompanyMembershipService::class)->updateMembership(
                            $this->getOwnerRecord(),
                            $record,
                            $data['role'],
                            (bool) ($data['is_primary'] ?? false),
                        );
                    }),
                Tables\Actions\DetachAction::make()
                    ->label(__('main.detach_company_member'))
                    ->modalHeading(__('main.detach_company_member'))
                    ->visible(fn (User $record): bool => (int) $this->getOwnerRecord()->user_id !== (int) $record->id),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label(__('main.detach_company_member')),
            ]);
    }
}
