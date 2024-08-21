<?php

namespace Modules\Administration\Filament\Resources;

use App\Models\City;
use Modules\Administration\Filament\Resources\CompanyResource\Pages;
use Modules\Administration\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\Country;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Livewire\Component as Livewire;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationGroup = 'Companies';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Main Info')
                    ->relationship('user')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                    ]),
                Forms\Components\RichEditor::make('description')->columnSpan('full'),
                Forms\Components\TextInput::make('ceo_name')->maxLength(255),
                Forms\Components\TextInput::make('tax_name')->required(),
                Forms\Components\FileUpload::make('logo')
                    ->label('logo')
                    ->directory('companies/logo')
                    ->storeFileNamesIn('image_path')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                        return (string) str($file->hashName());
                    })
                    ->image(),
                Forms\Components\TextInput::make('zipcode')->required(),
                Forms\Components\TextInput::make('national_address')->required(),
                Forms\Components\TextInput::make('website')->label('Website')
                    ->url()->maxLength(255),

                Select::make('country_id')
                    ->label('Country')
                    ->dehydrated(false)
                    ->searchable()
                    ->options(Country::pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set) => $set('state_id', null)),


                Select::make('state_id')
                    ->label('State')
                    ->placeholder(fn(Forms\Get $get): string => empty($get('country_id')) ? 'First select country' : 'Select an option')
                    ->options(function (?Company $record, Forms\Get $get, Forms\Set $set) {
                        if (!empty($record) && !empty($get('country_id'))) {
                            $set('country_id', $record->state->country_id);
                            $set('state_id', $record->state_id);
                        }
                        return State::where('country_id', $get('country_id'))->pluck('name', 'id');
                    })
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(fn($state, callable $set) => $set('city_id', null)),

                Select::make('city_id')
                    ->label('City')
                    ->placeholder(fn(Forms\Get $get): string => empty($get('state_id')) ? 'First select state' : 'Select an option')
                    ->options(function (?Company $record, Forms\Get $get, Forms\Set $set) {
                        if (!empty($record) && !empty($get('country_id'))) {
                            $set('state_id', $record->city->state_id);
                            $set('city_id', $record->city_id);
                        }

                        return City::where('state_id', $get('state_id'))->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),


                Forms\Components\TextInput::make('phone')->numeric()->minLength(8)->maxLength(11),
                Forms\Components\Repeater::make('contacts')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                config('administration.contacts.types.email') => config('administration.contacts.types.email'),
                                config('administration.contacts.types.phone') => config('administration.contacts.types.phone'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('contact')
                            ->label('Contact')
                            ->required()
                            ->maxLength(255)
                            ->rule(function ($get) {
                                return $get('type') === 'email' ? 'email' : 'regex:/^\+?[1-9]\d{1,14}$/';
                            }),
                    ])
                    ->minItems(1)
                    ->maxItems(10),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('user.email')->label('Email'),
                Tables\Columns\TextColumn::make('description')->html(),
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),
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
