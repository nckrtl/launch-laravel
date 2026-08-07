<?php

declare(strict_types=1);

namespace HardImpact\Launch\Setup\Auth;

use HardImpact\Launch\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyFortifyFilesTask extends Task
{
    /**
     * Create a new task instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    /**
     * Run the task.
     */
    public function run(): bool
    {
        $replacements = [
            '{{namespace}}' => app()->getNamespace(),
            '{{userModel}}' => config('auth.providers.users.model', 'App\\Models\\User'),
        ];

        // Copy FortifyServiceProvider
        $providerStubPath = __DIR__.'/../../../resources/stubs/auth/app/Providers/FortifyServiceProvider.php';
        $providerDestPath = app_path('Providers/FortifyServiceProvider.php');

        if (! $this->copyFile($providerStubPath, $providerDestPath, $replacements)) {
            $this->error('Failed to copy FortifyServiceProvider.');

            return false;
        }
        $this->info('FortifyServiceProvider copied successfully.');

        // Copy fortify config
        $configStubPath = __DIR__.'/../../../resources/stubs/auth/config/fortify.php';
        $configDestPath = config_path('fortify.php');

        if (! $this->copyFile($configStubPath, $configDestPath, $replacements)) {
            $this->error('Failed to copy fortify config.');

            return false;
        }
        $this->info('Fortify config copied successfully.');

        // Copy passkeys config
        $passkeysConfigStubPath = __DIR__.'/../../../resources/stubs/auth/config/passkeys.php';
        $passkeysConfigDestPath = config_path('passkeys.php');

        if (! $this->copyFile($passkeysConfigStubPath, $passkeysConfigDestPath, $replacements)) {
            $this->error('Failed to copy passkeys config.');

            return false;
        }
        $this->info('Passkeys config copied successfully.');

        // Copy Fortify Actions
        $actionsStubPath = __DIR__.'/../../../resources/stubs/auth/app/Actions/Fortify';
        $actionsDestPath = app_path('Actions/Fortify');

        if (! $this->copyDirectory($actionsStubPath, $actionsDestPath, $replacements)) {
            $this->error('Failed to copy Fortify actions.');

            return false;
        }
        $this->info('Fortify actions copied successfully.');

        // Copy Concerns (validation rules)
        $concernsStubPath = __DIR__.'/../../../resources/stubs/auth/app/Concerns';
        $concernsDestPath = app_path('Concerns');

        if (! $this->copyDirectory($concernsStubPath, $concernsDestPath, $replacements)) {
            $this->error('Failed to copy concerns.');

            return false;
        }
        $this->info('Concerns copied successfully.');

        // Copy Fortify responses
        $responsesStubPath = __DIR__.'/../../../resources/stubs/auth/app/Http/Responses';
        $responsesDestPath = app_path('Http/Responses');

        if (! $this->copyDirectory($responsesStubPath, $responsesDestPath, $replacements)) {
            $this->error('Failed to copy Fortify responses.');

            return false;
        }
        $this->info('Fortify responses copied successfully.');

        // Copy RequireSensitiveActionConfirmation middleware
        $sensitiveActionMiddlewareStubPath = __DIR__.'/../../../resources/stubs/auth/app/Http/Middleware/RequireSensitiveActionConfirmation.php';
        $sensitiveActionMiddlewareDestPath = app_path('Http/Middleware/RequireSensitiveActionConfirmation.php');

        if (! $this->copyFile($sensitiveActionMiddlewareStubPath, $sensitiveActionMiddlewareDestPath, $replacements)) {
            $this->error('Failed to copy RequireSensitiveActionConfirmation middleware.');

            return false;
        }
        $this->info('RequireSensitiveActionConfirmation middleware copied successfully.');

        // Copy TwoFactorAuthenticationController
        $tfaControllerStubPath = __DIR__.'/../../../resources/stubs/auth/app/Http/Controllers/Settings/TwoFactorAuthenticationController.php';
        $tfaControllerDestPath = app_path('Http/Controllers/Settings/TwoFactorAuthenticationController.php');

        if (! $this->copyFile($tfaControllerStubPath, $tfaControllerDestPath, $replacements)) {
            $this->error('Failed to copy TwoFactorAuthenticationController.');

            return false;
        }
        $this->info('TwoFactorAuthenticationController copied successfully.');

        // Copy TwoFactorAuthenticationRequest
        $tfaRequestStubPath = __DIR__.'/../../../resources/stubs/auth/app/Http/Requests/Settings/TwoFactorAuthenticationRequest.php';
        $tfaRequestDestPath = app_path('Http/Requests/Settings/TwoFactorAuthenticationRequest.php');

        if (! $this->copyFile($tfaRequestStubPath, $tfaRequestDestPath, $replacements)) {
            $this->error('Failed to copy TwoFactorAuthenticationRequest.');

            return false;
        }
        $this->info('TwoFactorAuthenticationRequest copied successfully.');

        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Copying Fortify provider, config, actions, and concerns';
    }
}
