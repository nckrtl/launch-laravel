<?php

declare(strict_types=1);

namespace HardImpact\Craft\Setup\Auth;

use HardImpact\Craft\Setup\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CleanupLegacyAuthVueFilesTask extends Task
{
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    public function run(): bool
    {
        foreach ($this->paths() as $path) {
            if ($this->filesystem->exists($path)) {
                $this->filesystem->delete($path);
            }
        }

        $viewsPath = resource_path('js/views');

        if ($this->filesystem->isDirectory($viewsPath)) {
            $this->filesystem->deleteDirectory($viewsPath);
        }

        $this->info('Legacy Vue auth files removed.');

        return true;
    }

    public function description(): string
    {
        return 'Removing legacy Vue auth files';
    }

    /**
     * @return list<string>
     */
    private function paths(): array
    {
        return [
            resource_path('js/components/TwoFactorRecoveryCodes.vue'),
            resource_path('js/components/TwoFactorSetupModal.vue'),
            resource_path('js/pages/auth/ConfirmPassword.vue'),
            resource_path('js/pages/auth/ForgotPassword.vue'),
            resource_path('js/pages/auth/Login.vue'),
            resource_path('js/pages/auth/Register.vue'),
            resource_path('js/pages/auth/ResetPassword.vue'),
            resource_path('js/pages/auth/TwoFactorChallenge.vue'),
            resource_path('js/pages/auth/VerifyEmail.vue'),
        ];
    }
}
