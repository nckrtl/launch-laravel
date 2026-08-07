<?php

declare(strict_types=1);

namespace NckRtl\Launch\Setup\Auth;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use NckRtl\Launch\Setup\Tasks\Task;

class UpdateUserModelTask extends Task
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
        $userModelPath = app_path('Models/User.php');

        if (! $this->filesystem->exists($userModelPath)) {
            $this->error('User model not found at '.$userModelPath);

            return false;
        }

        $content = $this->filesystem->get($userModelPath);

        $content = $this->ensureUseStatement($content, 'Laravel\\Fortify\\TwoFactorAuthenticatable');
        $content = $this->ensureUseStatement($content, 'Laravel\\Passkeys\\Contracts\\PasskeyUser');
        $content = $this->ensureUseStatement($content, 'Laravel\\Passkeys\\PasskeyAuthenticatable');

        if (! str_contains($content, 'implements PasskeyUser')) {
            $content = preg_replace(
                '/class User extends Authenticatable/',
                'class User extends Authenticatable implements PasskeyUser',
                $content,
                1
            ) ?? $content;
        }

        $content = $this->ensureTrait($content, 'TwoFactorAuthenticatable');
        $content = $this->ensureTrait($content, 'PasskeyAuthenticatable');
        $content = $this->normalizeAuthenticationImports($content);
        $content = $this->normalizeAuthenticationTraits($content);

        // Add two_factor fields to hidden array if not present
        if (! str_contains($content, 'two_factor_secret')) {
            $content = preg_replace(
                "/'password',/",
                "'password',\n        'two_factor_secret',\n        'two_factor_recovery_codes',",
                $content
            );
        }
        $content = $this->normalizeHiddenAttribute($content);

        // Add two_factor_confirmed_at to casts if not present
        if (! str_contains($content, 'two_factor_confirmed_at')) {
            // Try to add to casts method
            if (preg_match('/protected function casts\(\)[^{]*\{[^}]*return\s*\[/s', $content)) {
                $content = preg_replace(
                    "/(protected function casts\(\)[^{]*\{[^}]*return\s*\[)/s",
                    "$1\n            'two_factor_confirmed_at' => 'datetime',",
                    $content
                );
            }
        }

        if ($this->filesystem->put($userModelPath, $content) === false) {
            $this->error('Failed to update User model.');

            return false;
        }

        $this->info('User model updated with authentication traits.');

        return true;
    }

    private function ensureUseStatement(string $content, string $class): string
    {
        if (str_contains($content, "use {$class};")) {
            return $content;
        }

        if (preg_match_all('/^use [^;]+;/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $lastUse = end($matches[0]);
            $insertPosition = $lastUse[1] + strlen($lastUse[0]);

            return substr($content, 0, $insertPosition)."\nuse {$class};".substr($content, $insertPosition);
        }

        return $content;
    }

    private function ensureTrait(string $content, string $trait): string
    {
        if (preg_match('/class User[^{]*\{[^}]*use ([^;]+);/s', $content, $matches)) {
            $traits = array_map('trim', explode(',', $matches[1]));

            if (in_array($trait, $traits, true)) {
                return $content;
            }

            $traits[] = $trait;

            return str_replace('use '.$matches[1].';', 'use '.implode(', ', $traits).';', $content);
        }

        return preg_replace(
            '/(class User[^{]*\{)/',
            "$1\n    use {$trait};\n",
            $content,
            1
        ) ?? $content;
    }

    private function normalizeAuthenticationImports(string $content): string
    {
        $imports = [
            'Laravel\\Fortify\\TwoFactorAuthenticatable',
            'Laravel\\Passkeys\\Contracts\\PasskeyUser',
            'Laravel\\Passkeys\\PasskeyAuthenticatable',
        ];

        foreach ($imports as $import) {
            $content = preg_replace('/^use '.preg_quote($import, '/').';\\n/m', '', $content) ?? $content;
        }

        $block = implode('', array_map(fn (string $import): string => "use {$import};\n", $imports));

        return preg_replace(
            '/^use Illuminate\\\\Notifications\\\\Notifiable;\\n/m',
            "use Illuminate\\Notifications\\Notifiable;\n".$block,
            $content,
            1
        ) ?? $content;
    }

    private function normalizeAuthenticationTraits(string $content): string
    {
        return preg_replace(
            '/^    use HasFactory, Notifiable[^;]*;$/m',
            '    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;',
            $content,
            1
        ) ?? $content;
    }

    private function normalizeHiddenAttribute(string $content): string
    {
        return preg_replace(
            '/#\[Hidden\(\[[\s\S]*?\]\)\]/',
            "#[Hidden([\n    'password',\n    'two_factor_secret',\n    'two_factor_recovery_codes',\n    'remember_token',\n])]",
            $content,
            1
        ) ?? $content;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Updating User model with TwoFactorAuthenticatable trait';
    }
}
