<?php

namespace App\Filament\Resources;


use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationGroup(): ?string
    {
        return __('main.users_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.users');
    }

    public static function getModelLabel(): string
    {
        return __('main.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.users');
    }
    /*     public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->preload(),
                ]);
        } */

    /*     public static function table(Table $table): Table
        {
            return $table
                ->query(User::isNotCompany())
                ->columns([
                    Tables\Columns\TextColumn::make('name')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('email')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('roles.name')
                        ->label('Role')
                        ->searchable(),
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
        } */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('fields.name'))
                            ->minLength(2)->maxLength(15)->string()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('fields.email'))
                            ->email()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label(__('fields.phone_number'))
                            ->tel()
                            ->maxLength(25)
                            ->default(null),
                        Select::make('roles')
                            ->label(__('filament-shield::filament-shield.resource.label.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        TextInput::make('password')
                            ->label(__('fields.password'))
                            ->password()
                            ->required()
                            ->revealable()
                            ->hiddenOn('edit')
                            ->maxLength(255),
                        Hidden::make('email_verified_at')->default(now()),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('dashboard.id'))
                    ->sortable(),
                ImageColumn::make('image')
                    ->disk('public')
                    ->label(__('dashboard.image'))
                    ->circular(),
                TextColumn::make('type')
                    ->label(__('dashboard.type'))
                    ->badge()
                    ->color(function ($record) {
                        return $record->type == 'personal' ? 'info' : ($record->type == 'parent' ? 'danger' : ($record->type == 'specialist' ?? 'success'));
                    })
                    ->formatStateUsing(fn($record) =>
                        $record->type == 'personal' ? __('dashboard.personal') : ($record->type == 'parent' ? __('dashboard.parent') : ($record->type == 'specialist' ?? __('dashboard.specialist')))),
                TextColumn::make('name')
                    ->label(__('dashboard.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('dashboard.email'))
                    ->searchable(),
                TextColumn::make('phone_num')
                    ->label(__('dashboard.phone_number'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('filament-shield::filament-shield.resource.label.roles'))
                    ->badge()
                    ->color(function ($record) {
                        return $record->roles == 'owner' ? 'danger' : 'warning';
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subscription.start_date')
                    ->label(__('dashboard.subscription_start_date'))
                    ->badge()
                    ->dateTime('Y/m/d'),
                TextColumn::make('subscription.end_date')
                    ->label(__('dashboard.subscription_end_date'))
                    ->badge()
                    ->dateTime('Y/m/d'),
                TextColumn::make('created_at')
                    ->label(__('dashboard.created_at'))
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
