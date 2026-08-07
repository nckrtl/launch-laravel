<?php

declare(strict_types=1);

namespace {{namespace}}Filament\Pages\Auth;

use {{namespace}}Models\User;
use Filament\Actions;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Passkeys;

class EditProfile extends BaseEditProfile
{
    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)
            ->inlineLabel(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Tabs::make('Settings')
                    ->columnSpan(1)
                    ->tabs([
                        Tab::make('Profile')
                            ->schema([
                                $this->getNameFormComponent(),
                                $this->getEmailFormComponent(),
                            ]),
                        Tab::make('Security')
                            ->schema([
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                                $this->getCurrentPasswordFormComponent(),
                                SchemaActions::make([
                                    Actions\Action::make('enableTwoFactorAuthentication')
                                        ->label(fn (): string => $this->hasPendingTwoFactorAuthentication() ? 'Continue 2FA setup' : 'Set up 2FA')
                                        ->modalHeading('Set up 2FA')
                                        ->modalDescription('Scan the QR code with your authenticator app, save the recovery codes, then enter the generated code to finish setup.')
                                        ->modalWidth(Width::Large)
                                        ->modalSubmitActionLabel('Confirm 2FA')
                                        ->mountUsing(function (Schema $form): void {
                                            $this->ensureTwoFactorAuthenticationSetup();

                                            $user = $this->user()->refresh();

                                            $form->fill([
                                                'secret_key' => $this->decryptedTwoFactorSecret($user),
                                            ]);
                                        })
                                        ->schema(fn (Schema $schema): Schema => $schema
                                            ->dense()
                                            ->components([
                                                View::make('filament.user-security.two-factor')
                                                    ->viewData(fn (): array => [
                                                        'section' => 'setup',
                                                        'user' => $this->user()->refresh(),
                                                    ]),
                                                TextInput::make('secret_key')
                                                    ->label('Secret key')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->autocomplete('off'),
                                                View::make('filament.user-security.two-factor')
                                                    ->viewData(fn (): array => [
                                                        'section' => 'recovery',
                                                        'user' => $this->user()->refresh(),
                                                    ]),
                                                TextInput::make('code')
                                                    ->label('Authentication code')
                                                    ->required()
                                                    ->numeric()
                                                    ->length(6)
                                                    ->autocomplete('one-time-code'),
                                            ]))
                                        ->action(fn (array $data) => $this->confirmTwoFactorAuthentication((string) $data['code']))
                                        ->visible(fn (): bool => ! $this->hasConfirmedTwoFactorAuthentication()),
                                    Actions\Action::make('disableTwoFactorAuthentication')
                                        ->label('Disable 2FA')
                                        ->requiresConfirmation()
                                        ->color('warning')
                                        ->action(fn () => $this->resetTwoFactorAuthentication())
                                        ->visible(fn (): bool => $this->hasConfirmedTwoFactorAuthentication()),
                                ]),
                                View::make('filament.user-security.two-factor-status')
                                    ->viewData(fn (): array => [
                                        'user' => $this->user()->refresh(),
                                    ])
                                    ->visible(fn (): bool => $this->hasConfirmedTwoFactorAuthentication()),
                                View::make('filament.user-security.passkeys')
                                    ->visible(fn (): bool => $this->canManagePasskeys())
                                    ->viewData(fn (): array => [
                                        'user' => $this->user()->loadMissing('passkeys'),
                                    ]),
                            ]),
                        Tab::make('Appearance')
                            ->schema([
                                View::make('filament.user-security.appearance'),
                            ]),
                    ]),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                SchemaActions::make($this->getFormActions())
                    ->extraAttributes(['style' => 'margin-top: 1.5rem'])
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->sticky((! static::isSimple()) && $this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    protected function user(): User
    {
        return $this->getUser();
    }

    protected function hasPendingTwoFactorAuthentication(): bool
    {
        $user = $this->user();
        $attributes = $user->getAttributes();

        if (
            array_key_exists('two_factor_secret', $attributes)
            || array_key_exists('two_factor_recovery_codes', $attributes)
            || array_key_exists('two_factor_confirmed_at', $attributes)
        ) {
            return filled($attributes['two_factor_secret'] ?? null)
                || filled($attributes['two_factor_recovery_codes'] ?? null)
                || filled($attributes['two_factor_confirmed_at'] ?? null);
        }

        return $user->newQuery()
            ->whereKey($user->getKey())
            ->where(fn ($query) => $query
                ->whereNotNull('two_factor_secret')
                ->orWhereNotNull('two_factor_recovery_codes')
                ->orWhereNotNull('two_factor_confirmed_at'))
            ->exists();
    }

    protected function hasConfirmedTwoFactorAuthentication(): bool
    {
        $user = $this->user();

        if (method_exists($user, 'hasEnabledTwoFactorAuthentication')) {
            return $user->hasEnabledTwoFactorAuthentication();
        }

        return filled($user->two_factor_confirmed_at);
    }

    protected function canManagePasskeys(): bool
    {
        return class_exists(Passkeys::class) && method_exists($this->user(), 'passkeys');
    }

    protected function decryptedTwoFactorSecret(User $user): ?string
    {
        if (blank($user->two_factor_secret)) {
            return null;
        }

        return Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
    }

    protected function ensureTwoFactorAuthenticationSetup(): void
    {
        if ($this->hasPendingTwoFactorAuthentication()) {
            return;
        }

        app(EnableTwoFactorAuthentication::class)($this->user());

        $this->user()->refresh();
    }

    protected function confirmTwoFactorAuthentication(string $code): void
    {
        try {
            app(ConfirmTwoFactorAuthentication::class)($this->user(), $code);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages([
                'code' => $exception->errors()['code'][0] ?? __('The provided two factor authentication code was invalid.'),
            ]);
        }

        $this->user()->refresh();
    }

    protected function resetTwoFactorAuthentication(): void
    {
        $this->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
