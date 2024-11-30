<?php
namespace App\Filament\Pages;

use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use DB;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class Registration extends Register
{
    protected ?string $maxWidth = '6xl';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('account')
                        ->label(__('main.account'))
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            $this->getPhoneFormComponent(),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                    Wizard\Step::make('company')
                        ->label(__('main.company'))
                        ->columns(2)
                        ->schema([
                            TextInput::make('companyName')
                                ->label(__('fields.name'))
                                ->string()
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

                            Select::make('country_id')
                                ->label(__('fields.country'))
                                ->options(Country::all()->pluck('name', 'id'))
                                ->exists('countries', 'id')
                                ->live()->preload()->searchable()->required()->reactive()
                                ->afterStateUpdated(fn(callable $set, $state) => $set('state_id', null)),

                            Select::make('state_id')
                                ->label(__('fields.state'))
                                ->exists('states', 'id')
                                ->reactive()->required()->preload()->live()
                                ->options(
                                    fn(Get $get) =>
                                    State::where('country_id', $get('country_id'))->pluck('name', 'id')
                                )
                                ->disabled(fn(Get $get) => $get('country_id') ? false : true)
                                ->afterStateUpdated(fn(callable $set, $state) => $set('city_id', null))
                                ->searchable(static fn(Select $component) => !$component->isDisabled()),

                            Select::make('city_id')
                                ->label(__('fields.city'))
                                ->reactive()->live()->preload()
                                ->exists('cities', 'id')
                                ->options(
                                    fn(Get $get) =>
                                    City::where('state_id', $get('state_id'))->pluck('name', 'id')
                                )
                                ->disabled(fn(Get $get) => $get('country_id') && $get('state_id') ? false : true)
                                ->searchable(static fn(Select $component) => !$component->isDisabled()),

                            TextInput::make('national_address')
                                ->string()
                                ->label(__('fields.national_address')),
                            TextInput::make('zipcode')
                                ->numeric()
                                ->label(__('fields.zip_code'))
                                ->required(),
                        ]),
                    /*                    Wizard\Step::make('subscription')
                                           ->label(__('main.subscription'))
                                           ->schema([
                                               $this->getPasswordFormComponent(),
                                               $this->getPasswordConfirmationFormComponent(),
                                           ]), */
                ])->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        type="submit"
                        size="sm"
                        wire:submit="register"
                    >
                        @lang(__('general.register'))
                    </x-filament::button>
                    BLADE))),
            ]);
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }
        DB::beginTransaction();

        $data = $this->form->getState();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'],
            'isCompany' => true
        ]);

        Company::create([
            'name' => $data['companyName'],
            'phone' => $data['companyPhone'],
            'website' => $data['website'],
            'ceo_name' => $data['ceo_name'],
            'tax_name' => $data['tax_name'],
            'country_id' => $data['country_id'],
            'state_id' => $data['state_id'],
            'city_id' => $data['city_id'],
            'national_address' => $data['national_address'],
            'zip_code' => $data['zipcode'],
        ]);

        DB::commit();

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        return redirect()->to('/')->with('success', __(''));

    }
    public function getFormActions(): array
    {
        return [];
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::pages/auth/register.actions.login.label'))
            ->url('/');
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone_number')
            ->label(__('fields.phone'))
            ->tel()
            ->maxLength(255);
    }
}