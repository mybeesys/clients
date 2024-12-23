<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    public static function getNavigationGroup(): ?string
    {
        return __('main.companies_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.companies');
    }

    public static function getModelLabel(): string
    {
        return __('main.company');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.companies');
    }

    /*    public static function getEloquentQuery(): Builder
       {
           return parent::getEloquentQuery()->with('user');
       } */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('companyName')
                            ->label(__('fields.name'))
                            ->string()
                            ->unique('companies', 'name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('companyPhone')
                            ->label(__('fields.phone'))
                            ->tel()->minLength(8)->maxLength(11),
                        TextInput::make('website')
                            ->label(__('fields.website'))
                            ->url()
                            ->maxLength(255),
                        TextInput::make('ceo_name')
                            ->label(__('fields.ceo_name'))
                            ->maxLength(255),
                        TextInput::make('tax_name')
                            ->label(__('fields.tax_name'))
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label(__('fields.user'))
                            ->relationship('user', 'email')
                            ->exists('users', 'id')
                            ->searchable()
                            ->preload()
                            ->required()

                    ]),
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        Select::make('country_id')
                            ->label(__('fields.country'))
                            ->relationship('country', 'name_en')
                            ->exists('countries', 'id')
                            ->live()->preload()->searchable()->required(),
                        TextInput::make('state')
                            ->label(__('fields.state'))
                            ->string()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label(__('fields.city'))
                            ->string()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('national_address')
                            ->string()
                            ->label(__('fields.national_address')),
                        TextInput::make('zipcode')
                            ->numeric()
                            ->label(__('fields.zip_code'))
                            ->required(),
                    ]),
                Section::make()
                    ->columns(2)
                    ->schema([
                        Textarea::make('description')
                            ->label(__('fields.description')),
                        FileUpload::make('logo')
                            ->label(__('fields.logo'))
                            ->image()
                            ->directory('companies/logos'),
                    ])
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('fields.description'))
                    ->searchable(),
                TextColumn::make('ceo_name')
                    ->label(__('fields.ceo_name'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('fields.phone')),
                TextColumn::make('zipcode')
                    ->label(__('fields.zip_code'))
                    ->searchable(),
                TextColumn::make('national_address')
                    ->label(__('fields.national_address'))
                    ->searchable(),
                TextColumn::make('website')
                    ->label(__('fields.website'))
                    ->searchable(),
                TextColumn::make('country.name_ar')
                    ->label(__('fields.country'))
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label(__('fields.state'))
                    ->sortable(),
                TextColumn::make('city.name')
                    ->label(__('fields.city'))
                    ->sortable(),
                TextColumn::make('tax_name')
                    ->label(__('fields.tax_name'))
                    ->searchable(),
                TextColumn::make('logo')
                    ->label(__('fields.logo'))
                    ->searchable(),
                IconColumn::make('subscribed')
                    ->label(__('fields.has_subscription'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('fields.updated_at'))
                    ->dateTime()
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
