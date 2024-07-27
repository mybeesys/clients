<?php

namespace Modules\Administration\Filament\Resources;

use Modules\Administration\Filament\Resources\CompanyResource\Pages;
use Modules\Administration\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

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
                Forms\Components\RichEditor::make('description')->columnSpan('full'),
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

                Forms\Components\TextInput::make('ceo_name')->maxLength(255),
                Forms\Components\TextInput::make('tax_name')->required(),
                // Forms\Components\FileUpload::make('logo')
                //     ->label('Logo')
                //     ->image()
                //     ->directory('companies/logo')
                //     ->required(),
                Forms\Components\TextInput::make('zipcode')->required(),
                Forms\Components\TextInput::make('national_address')->required(),
                Forms\Components\TextInput::make('website')->label('Website')
                    ->url()->maxLength(255),
                Forms\Components\Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\TextInput::make('phone')->numeric()->minLength(8)->maxLength(11),
                Forms\Components\Repeater::make('contacts')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
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
                //
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
