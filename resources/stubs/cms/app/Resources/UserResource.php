<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Passkeys\Passkeys;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->rule(Password::defaults()),
                    ]),
                Section::make('Security administration')
                    ->visible(fn (User $record): bool => ! self::isCurrentUser($record))
                    ->schema([
                        SchemaActions::make([
                            Actions\Action::make('resetTwoFactorAuthentication')
                                ->label('Reset 2FA')
                                ->requiresConfirmation()
                                ->color('warning')
                                ->visible(fn (User $record): bool => ! self::isCurrentUser($record) && self::hasTwoFactorAuthentication($record))
                                ->action(fn (User $record) => self::resetTwoFactorAuthentication($record)),
                            Actions\Action::make('resetPasskeys')
                                ->label('Reset passkeys')
                                ->requiresConfirmation()
                                ->color('warning')
                                ->visible(fn (User $record): bool => ! self::isCurrentUser($record) && self::hasPasskeys($record))
                                ->action(fn (User $record) => self::resetPasskeys($record)),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->state(fn (User $record): bool => filled($record->two_factor_confirmed_at))
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Enabled' : 'Not set up')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('created_at'),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('resetTwoFactorAuthentication')
                    ->label('Reset 2FA')
                    ->requiresConfirmation()
                    ->color('warning')
                    ->visible(fn (User $record): bool => self::hasTwoFactorAuthentication($record))
                    ->action(fn (User $record) => self::resetTwoFactorAuthentication($record)),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function hasTwoFactorAuthentication(User $user): bool
    {
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

    public static function isCurrentUser(User $user): bool
    {
        return auth()->id() === $user->getKey();
    }

    public static function canManagePasskeys(User $user): bool
    {
        return class_exists(Passkeys::class) && method_exists($user, 'passkeys');
    }

    public static function hasPasskeys(User $user): bool
    {
        if (! method_exists($user, 'passkeys')) {
            return false;
        }

        if ($user->relationLoaded('passkeys')) {
            return $user->passkeys->isNotEmpty();
        }

        return $user->passkeys()->exists();
    }

    public static function resetTwoFactorAuthentication(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public static function resetPasskeys(User $user): void
    {
        if (! method_exists($user, 'passkeys')) {
            return;
        }

        $user->passkeys()->delete();
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
