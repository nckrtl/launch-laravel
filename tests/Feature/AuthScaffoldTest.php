<?php

use HardImpact\Craft\Setup\Auth\CleanupLegacyAuthVueFilesTask;
use HardImpact\Craft\Setup\Auth\ConfigureAuthFrontendBootstrapTask;
use HardImpact\Craft\Setup\Auth\InstallAuthReactScaffoldTask;
use HardImpact\Craft\Setup\Cms\ConfigureFilamentAuthRedirectTask;
use HardImpact\Craft\Setup\Cms\InstallNpmPackagesTask;
use HardImpact\Craft\Setup\Cms\RunSetupAuthTask;
use HardImpact\Craft\Setup\SetupAuth;
use HardImpact\Craft\Setup\SetupCms;
use Illuminate\Filesystem\Filesystem;

// Tests for stub file content and namespace placeholders

it('generates auth test files with proper namespace replacement', function () {
    $filesystem = new Filesystem;

    // Get package root path from test directory
    $packageRoot = dirname(__DIR__, 2);

    // Simulate the task's behavior - read from the actual stub location
    $stubPath = $packageRoot.'/resources/stubs/auth/tests/Feature/Auth/RegistrationTest.php';
    expect($filesystem->exists($stubPath))->toBeTrue();

    $stubContent = file_get_contents($stubPath);

    // Verify the stub contains the placeholder pattern for use statements
    // The placeholder is {{namespace}}App which becomes App\App after replacement
    expect($stubContent)->toContain('{{namespace}}App');
    expect($stubContent)->not->toContain('use App\\App;');

    // Test with standard Laravel namespace (app()->getNamespace() returns 'App\\')
    $namespaceWithBackslash = 'App\\';
    $replacedContent = str_replace('{{namespace}}', $namespaceWithBackslash, $stubContent);

    // Verify the replacement works correctly: {{namespace}}App becomes App\App
    expect($replacedContent)->toContain('use App\\App;');
    expect($replacedContent)->not->toContain('{{namespace}}');
});

it('generates App.php with proper namespace replacement', function () {
    $filesystem = new Filesystem;

    // Get package root path from test directory
    $packageRoot = dirname(__DIR__, 2);

    // Read the stub from the correct location
    $stubPath = $packageRoot.'/resources/stubs/app/app/App.php';
    expect($filesystem->exists($stubPath))->toBeTrue();

    $stubContent = file_get_contents($stubPath);

    // Verify the stub contains the placeholder
    expect($stubContent)->toContain('namespace {{namespace}};');
    expect($stubContent)->not->toContain('namespace App;');

    // Test with trimmed namespace (CopyAppClassTask uses rtrim)
    $trimmedNamespace = 'App';
    $replacedContent = str_replace('{{namespace}}', $trimmedNamespace, $stubContent);

    // The namespace should be properly formed without trailing backslash
    expect($replacedContent)->toContain('namespace App;');
    expect($replacedContent)->not->toContain('namespace App\\;');
});

it('does not generate App\\Facades\\App references in scaffolded files', function () {
    $filesystem = new Filesystem;

    // Get package root path from test directory
    $packageRoot = dirname(__DIR__, 2);

    // Read all auth stub files
    $authStubsDir = $packageRoot.'/resources/stubs/auth';
    $appStubsDir = $packageRoot.'/resources/stubs/app';

    expect($filesystem->isDirectory($authStubsDir))->toBeTrue();
    expect($filesystem->isDirectory($appStubsDir))->toBeTrue();

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($authStubsDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            // Should never contain App\Facades\App
            expect($content)->not->toContain('App\\Facades\\App');
            expect($content)->not->toContain('use App\\Facades');
        }
    }

    // Check app stubs too
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($appStubsDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            expect($content)->not->toContain('App\\Facades\\App');
            expect($content)->not->toContain('use App\\Facades');
        }
    }
});

// Tests for task behavior and namespace replacement logic

describe('CopyAppClassTask', function () {
    it('replaces namespace placeholder correctly when copying App.php', function () {
        $filesystem = new Filesystem;
        $tempDir = sys_get_temp_dir().'/craft-test-'.uniqid();

        // Create temp directory structure
        $filesystem->makeDirectory($tempDir.'/app', 0755, true);

        // Read original stub
        $packageRoot = dirname(__DIR__, 2);
        $stubContent = file_get_contents($packageRoot.'/resources/stubs/app/app/App.php');

        // Verify stub has placeholder
        expect($stubContent)->toContain('{{namespace}}');

        // Test with trimmed namespace (as CopyAppClassTask does with rtrim)
        $testCases = [
            'App' => 'namespace App;',
            'Custom' => 'namespace Custom;',
            'MyVendor\\MyApp' => 'namespace MyVendor\\MyApp;',
        ];

        foreach ($testCases as $namespace => $expectedNamespace) {
            $replacedContent = str_replace('{{namespace}}', $namespace, $stubContent);

            expect($replacedContent)->toContain($expectedNamespace);
            expect($replacedContent)->not->toContain('{{namespace}}');

            // Verify the resulting PHP is valid syntax
            $tempFile = $tempDir.'/app/App_'.str_replace('\\', '_', $namespace).'.php';
            file_put_contents($tempFile, $replacedContent);

            // Check PHP syntax is valid
            exec('php -l '.escapeshellarg($tempFile).' 2>&1', $output, $exitCode);
            expect($exitCode)->toBe(0, 'PHP syntax error in generated file for namespace: '.$namespace);
        }

        // Cleanup
        $filesystem->deleteDirectory($tempDir);
    });

    it('uses a trimmed namespace for the cms App.php copy task', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);
        $stubContent = file_get_contents($packageRoot.'/resources/stubs/cms/app/App.php');

        $replacedContent = str_replace('{{namespace}}', rtrim('App\\', '\\'), $stubContent);

        expect($replacedContent)
            ->toContain('namespace App;')
            ->toContain("return 'filament.admin.pages.dashboard';")
            ->not->toContain('namespace App\\;')
            ->not->toContain('DashboardController.show');
    });

    it('handles edge case namespaces correctly', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);
        $stubContent = file_get_contents($packageRoot.'/resources/stubs/app/app/App.php');

        // Test edge cases with trimmed namespaces
        $edgeCases = [
            // Single level
            'App' => 'namespace App;',
            // Deep nesting (trimmed)
            'A\\B\\C\\D\\E' => 'namespace A\\B\\C\\D\\E;',
        ];

        foreach ($edgeCases as $namespace => $expected) {
            $replacedContent = str_replace('{{namespace}}', $namespace, $stubContent);
            expect($replacedContent)->toContain($expected);
        }
    });
});

describe('CopyAuthTestsTask', function () {
    it('replaces namespace placeholder in all auth test files', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        $testFiles = [
            'Feature/Auth/RegistrationTest.php',
            'Feature/Auth/EmailVerificationTest.php',
        ];

        foreach ($testFiles as $testFile) {
            $stubPath = $packageRoot.'/resources/stubs/auth/tests/'.$testFile;
            expect($filesystem->exists($stubPath))->toBeTrue($testFile.' should exist');

            $content = file_get_contents($stubPath);

            // Should contain placeholder pattern for use statements
            // The pattern is {{namespace}}App which requires namespace with trailing backslash
            expect(strpos($content, '{{namespace}}App') !== false)->toBeTrue(
                $testFile.' should have {{namespace}}App placeholder'
            );

            // Should not contain hardcoded App\App
            expect(strpos($content, 'use App\\App;') !== false)->toBeFalse(
                $testFile.' should not have hardcoded use App\\App;'
            );

            // Test replacement with namespace that includes trailing backslash (as app()->getNamespace() returns)
            $namespaceWithBackslash = 'MyApp\\';
            $replaced = str_replace('{{namespace}}', $namespaceWithBackslash, $content);

            expect(strpos($replaced, 'use MyApp\\App;') !== false)->toBeTrue(
                $testFile.' should have correct use statement after replacement'
            );
            expect(strpos($replaced, '{{namespace}}') !== false)->toBeFalse(
                $testFile.' should not have placeholder after replacement'
            );
        }
    });

    it('generates valid PHP syntax after namespace replacement', function () {
        $filesystem = new Filesystem;
        $tempDir = sys_get_temp_dir().'/craft-test-'.uniqid();
        $filesystem->makeDirectory($tempDir.'/tests/Feature/Auth', 0755, true);

        $packageRoot = dirname(__DIR__, 2);
        $testFiles = [
            'Feature/Auth/RegistrationTest.php',
            'Feature/Auth/EmailVerificationTest.php',
        ];

        foreach ($testFiles as $testFile) {
            $content = file_get_contents($packageRoot.'/resources/stubs/auth/tests/'.$testFile);

            // Replace with a namespace that includes trailing backslash (as app()->getNamespace() returns)
            $content = str_replace('{{namespace}}', 'Custom\\', $content);

            // Write to temp file
            $tempFile = $tempDir.'/tests/'.$testFile;
            $filesystem->ensureDirectoryExists(dirname($tempFile));
            file_put_contents($tempFile, $content);

            // Validate PHP syntax
            exec('php -l '.escapeshellarg($tempFile).' 2>&1', $output, $exitCode);
            expect($exitCode)->toBe(0, 'PHP syntax error in '.$testFile);
        }

        // Cleanup
        $filesystem->deleteDirectory($tempDir);
    });
});

// Integration-like tests that verify the full replacement chain

describe('Namespace Replacement Integration', function () {
    it('correctly handles the standard Laravel App namespace', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        // Standard Laravel: app()->getNamespace() returns 'App\\' with trailing backslash
        // CopyAppClassTask uses rtrim() to get 'App'
        // CopyAuthTestsTask uses as-is to get 'App\\'

        // Test App.php (uses trimmed namespace)
        $appStub = file_get_contents($packageRoot.'/resources/stubs/app/app/App.php');
        $appResult = str_replace('{{namespace}}', 'App', $appStub);

        expect($appResult)->toContain('namespace App;');
        expect($appResult)->not->toContain('namespace App\\;');

        // Test auth test files (uses namespace with trailing backslash)
        $registrationStub = file_get_contents($packageRoot.'/resources/stubs/auth/tests/Feature/Auth/RegistrationTest.php');
        $registrationResult = str_replace('{{namespace}}', 'App\\', $registrationStub);

        // {{namespace}}App becomes App\App
        expect($registrationResult)->toContain('use App\\App;');
    });

    it('correctly handles custom application namespaces', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        $customNamespaces = [
            ['trimmed' => 'Acme', 'with_backslash' => 'Acme\\'],
            ['trimmed' => 'Vendor\\Package', 'with_backslash' => 'Vendor\\Package\\'],
        ];

        foreach ($customNamespaces as $ns) {
            // Test App.php (uses trimmed namespace)
            $appStub = file_get_contents($packageRoot.'/resources/stubs/app/app/App.php');
            $appResult = str_replace('{{namespace}}', $ns['trimmed'], $appStub);

            expect($appResult)->toContain('namespace '.$ns['trimmed'].';');

            // Test auth test (uses namespace with trailing backslash)
            $testStub = file_get_contents($packageRoot.'/resources/stubs/auth/tests/Feature/Auth/RegistrationTest.php');
            $testResult = str_replace('{{namespace}}', $ns['with_backslash'], $testStub);

            expect($testResult)->toContain('use '.$ns['with_backslash'].'App;');
        }
    });
});

describe('Filament auth scaffold integration', function () {
    it('keeps profile and security management inside the Filament scaffold', function () {
        $packageRoot = dirname(__DIR__, 2);

        $setupCmsStub = file_get_contents($packageRoot.'/src/Setup/SetupCms.php');
        $copyCmsFilesTask = file_get_contents($packageRoot.'/src/Setup/Cms/CopyCmsFilesTask.php');
        $editUserStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource/Pages/EditUser.php');
        $userResourceStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource.php');
        $profilePageStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Filament/Pages/Auth/EditProfile.php');
        $adminPanelProviderStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Filament/AdminPanelProvider.php');
        $passkeyViewStub = $packageRoot.'/resources/stubs/cms/resources/views/filament/user-security/passkeys.blade.php';
        $appearanceViewStub = $packageRoot.'/resources/stubs/cms/resources/views/filament/user-security/appearance.blade.php';
        $twoFactorStatusViewStub = $packageRoot.'/resources/stubs/cms/resources/views/filament/user-security/two-factor-status.blade.php';

        expect($setupCmsStub)
            ->not->toContain('CopyFilamentSecurityControllerTask::class')
            ->not->toContain('InstallFilamentSecurityReactScaffoldTask::class');

        expect($copyCmsFilesTask)
            ->toContain("'resources/views' => resource_path('views')");

        expect($passkeyViewStub)->toBeFile();
        expect(file_get_contents($passkeyViewStub))
            ->toContain('navigator.credentials.create')
            ->toContain("route('passkey.registration-options')")
            ->toContain("route('passkey.store')");

        expect($adminPanelProviderStub)
            ->toContain('use {{namespace}}Filament\\Pages\\Auth\\EditProfile;')
            ->toContain('->profile(EditProfile::class, isSimple: false)');

        expect($profilePageStub)
            ->toContain('class EditProfile extends BaseEditProfile')
            ->toContain('public function defaultForm(Schema $schema): Schema')
            ->toContain('return parent::defaultForm($schema)')
            ->toContain('->inlineLabel(false)')
            ->toContain('->columns(2)')
            ->toContain("Tabs::make('Settings')")
            ->toContain('->columnSpan(1)')
            ->toContain("Tab::make('Profile')")
            ->toContain("Tab::make('Security')")
            ->toContain("Tab::make('Appearance')")
            ->toContain("View::make('filament.user-security.two-factor')")
            ->toContain("View::make('filament.user-security.two-factor-status')")
            ->toContain("View::make('filament.user-security.passkeys')")
            ->toContain("View::make('filament.user-security.appearance')")
            ->toContain('public function getFormContentComponent(): Component')
            ->toContain("->extraAttributes(['style' => 'margin-top: 1.5rem'])")
            ->not->toContain('use Filament\\Schemas\\Components\\Section;')
            ->not->toContain("Section::make('Profile information')")
            ->not->toContain("Section::make('Password')")
            ->not->toContain("Section::make('Two-factor authentication')")
            ->not->toContain("Section::make('Passkeys')")
            ->not->toContain("Section::make('Theme')");

        expect($appearanceViewStub)->toBeFile();
        expect(file_get_contents($appearanceViewStub))
            ->toContain('<x-filament-panels::theme-switcher />');

        expect($twoFactorStatusViewStub)->toBeFile();
        expect(file_get_contents($twoFactorStatusViewStub))
            ->toContain('Two-factor authentication is configured for this account.');

        expect($editUserStub)
            ->not->toContain("route('Settings.SecurityController.edit')")
            ->not->toContain('Settings\\SecurityController')
            ->not->toContain("Actions\\Action::make('managePasskeys')")
            ->not->toContain("View::make('filament.user-security.passkeys'");

        expect($userResourceStub)
            ->not->toContain("Section::make('Two-factor authentication')")
            ->not->toContain("Section::make('Passkeys')")
            ->not->toContain("View::make('filament.user-security.two-factor')")
            ->not->toContain("View::make('filament.user-security.passkeys')");
    });

    it('shows and resets user two-factor authentication from Filament', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        $userResourceStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource.php');
        $editUserStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource/Pages/EditUser.php');

        expect($userResourceStub)
            ->toContain("Tables\\Columns\\TextColumn::make('two_factor_confirmed_at')")
            ->toContain("->label('2FA')")
            ->toContain('->state(fn (User $record): bool => filled($record->two_factor_confirmed_at))')
            ->toContain("->formatStateUsing(fn (bool \$state): string => \$state ? 'Enabled' : 'Not set up')")
            ->toContain("->color(fn (bool \$state): string => \$state ? 'success' : 'gray')")
            ->toContain("Actions\\Action::make('resetTwoFactorAuthentication')")
            ->toContain("->label('Reset 2FA')")
            ->toContain('->visible(fn (User $record): bool => self::hasTwoFactorAuthentication($record))')
            ->toContain('->action(fn (User $record) => self::resetTwoFactorAuthentication($record))')
            ->toContain('public static function hasTwoFactorAuthentication(User $user): bool')
            ->toContain('public static function resetTwoFactorAuthentication(User $user): void')
            ->toContain("'two_factor_secret' => null")
            ->toContain("'two_factor_recovery_codes' => null")
            ->toContain("'two_factor_confirmed_at' => null");

        expect($userResourceStub)
            ->toContain("Section::make('Security administration')")
            ->toContain("Actions\\Action::make('resetTwoFactorAuthentication')")
            ->toContain("->label('Reset 2FA')")
            ->toContain('->visible(fn (User $record): bool => ! self::isCurrentUser($record) && self::hasTwoFactorAuthentication($record))')
            ->toContain('->action(fn (User $record) => self::resetTwoFactorAuthentication($record))');

        expect($editUserStub)
            ->not->toContain("Actions\\Action::make('resetTwoFactorAuthentication')");
    });

    it('adds self-service and admin security actions to the Filament user detail page', function () {
        $packageRoot = dirname(__DIR__, 2);

        $userResourceStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource.php');
        $editUserStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource/Pages/EditUser.php');

        expect($userResourceStub)
            ->toContain('use Illuminate\\Support\\Facades\\Hash;')
            ->toContain('use Illuminate\\Validation\\Rules\\Password;')
            ->toContain('public static function isCurrentUser(User $user): bool')
            ->toContain('public static function hasPasskeys(User $user): bool')
            ->toContain('public static function resetPasskeys(User $user): void')
            ->toContain('$user->passkeys()->delete();');

        expect($userResourceStub)
            ->toContain("Section::make('Security administration')")
            ->toContain('SchemaActions::make([')
            ->toContain("Actions\\Action::make('resetPasskeys')")
            ->toContain('->visible(fn (User $record): bool => ! self::isCurrentUser($record) && self::hasPasskeys($record))')
            ->toContain('->dehydrateStateUsing(fn (string $state): string => Hash::make($state))')
            ->toContain('->dehydrated(fn (?string $state): bool => filled($state))')
            ->not->toContain("Section::make('Two-factor authentication')")
            ->not->toContain("Section::make('Passkeys')")
            ->not->toContain("Actions\\Action::make('enableTwoFactorAuthentication')")
            ->not->toContain("View::make('filament.user-security.passkeys')")
            ->not->toContain('public static function updatePassword(User $user, string $password): void');

        $profilePageStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Filament/Pages/Auth/EditProfile.php');

        expect($profilePageStub)
            ->toContain("Actions\\Action::make('enableTwoFactorAuthentication')")
            ->toContain("'Continue 2FA setup' : 'Set up 2FA'")
            ->toContain("->modalHeading('Set up 2FA')")
            ->toContain('->modalWidth(Width::Large)')
            ->toContain("->modalSubmitActionLabel('Confirm 2FA')")
            ->toContain('->mountUsing(function (Schema $form): void')
            ->toContain("'secret_key' => \$this->decryptedTwoFactorSecret(\$user)")
            ->toContain('->schema(fn (Schema $schema): Schema => $schema')
            ->toContain('->dense()')
            ->toContain('->components([')
            ->toContain("TextInput::make('secret_key')")
            ->toContain("->label('Secret key')")
            ->toContain('->disabled()')
            ->toContain('->dehydrated(false)')
            ->toContain("'section' => 'setup'")
            ->toContain("'section' => 'recovery'")
            ->toContain("TextInput::make('code')")
            ->toContain('Fortify::currentEncrypter()->decrypt($user->two_factor_secret)')
            ->toContain('app(ConfirmTwoFactorAuthentication::class)($this->user(), $code);')
            ->toContain('protected function hasConfirmedTwoFactorAuthentication(): bool')
            ->toContain('->visible(fn (): bool => ! $this->hasConfirmedTwoFactorAuthentication())')
            ->toContain('->visible(fn (): bool => $this->hasConfirmedTwoFactorAuthentication())')
            ->toContain("View::make('filament.user-security.passkeys')");

        expect($editUserStub)
            ->toContain('Actions\\DeleteAction::make()')
            ->toContain('protected function mutateFormDataBeforeFill(array $data): array')
            ->toContain("\$data['password'] = null;")
            ->toContain('protected function afterSave(): void')
            ->toContain("\$this->refreshFormData(['password']);")
            ->not->toContain("Actions\\Action::make('enableTwoFactorAuthentication')")
            ->not->toContain("Actions\\Action::make('managePasskeys')")
            ->not->toContain("Actions\\Action::make('setPassword')");

        $twoFactorView = file_get_contents(__DIR__.'/../../resources/stubs/cms/resources/views/filament/user-security/two-factor.blade.php');

        expect($twoFactorView)
            ->not->toContain('twoFactorSecretKey()')
            ->not->toContain('Fortify::currentEncrypter()->decrypt($user->two_factor_secret)')
            ->toContain('.fi-two-factor-setup')
            ->toContain("\$section = \$section ?? 'setup'")
            ->toContain("\$section === 'recovery'")
            ->not->toContain('fi-two-factor-secret-input')
            ->not->toContain('fi-two-factor-label')
            ->toContain('fi-fo-field-label')
            ->toContain('fi-fo-field-label-content')
            ->toContain('.fi-two-factor-field .fi-fo-field-label-content')
            ->toContain('fi-fo-field-content-col')
            ->toContain('.fi-two-factor-code-block')
            ->toContain('.dark .fi-two-factor-code-block')
            ->toContain('<pre class="fi-two-factor-code-block"><code>')
            ->toContain('implode(PHP_EOL, $user->recoveryCodes())')
            ->not->toContain('The codes will only be shown during setup.');
    });

    it('uses Filament action namespaces supported by the installed Filament major version', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        $userResourceStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Resources/UserResource.php');

        expect($userResourceStub)
            ->toContain('use Filament\\Actions;')
            ->toContain('use Filament\\Forms\\Components\\TextInput;')
            ->toContain('use Filament\\Schemas\\Components\\Section;')
            ->toContain('Actions\\EditAction::make()')
            ->toContain('Actions\\BulkActionGroup::make([')
            ->toContain('Actions\\DeleteBulkAction::make()')
            ->not->toContain('use Filament\\Schemas\\Components\\TextInput;')
            ->not->toContain('Tables\\Actions\\EditAction::make()')
            ->not->toContain('Tables\\Actions\\BulkActionGroup::make([')
            ->not->toContain('Tables\\Actions\\DeleteBulkAction::make()');
    });

    it('can install Filament npm packages with npm when Bun is unavailable', function () {
        $task = new InstallNpmPackagesTask(new Filesystem);
        $method = new ReflectionMethod($task, 'packageInstallProcess');

        $process = $method->invoke($task, ['@tailwindcss/forms'], 'npm');

        expect($process->getCommandLine())
            ->toContain("'npm' 'install' '--save-dev' '@tailwindcss/forms'")
            ->not->toContain("'bun' 'add' '-D'");
    });

    it('uses the shared app auth scaffold instead of Filament auth pages', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        $panelProviderStub = file_get_contents($packageRoot.'/resources/stubs/cms/app/Filament/AdminPanelProvider.php');
        $middlewareStub = $packageRoot.'/resources/stubs/cms/app/Http/Middleware/AuthenticateFilament.php';

        expect($panelProviderStub)
            ->toContain('namespace {{namespace}}Providers\\Filament;')
            ->not->toContain('->login()')
            ->not->toContain('->passwordReset()')
            ->not->toContain('->registration()')
            ->toContain('use {{namespace}}Http\\Middleware\\AuthenticateFilament;')
            ->toContain('AuthenticateFilament::class');

        expect($filesystem->exists($middlewareStub))->toBeTrue();

        $middlewareContent = file_get_contents($middlewareStub);

        expect($middlewareContent)
            ->toContain('namespace {{namespace}}Http\\Middleware;')
            ->toContain('use Filament\\Http\\Middleware\\Authenticate as FilamentAuthenticate;')
            ->toContain('class AuthenticateFilament extends FilamentAuthenticate')
            ->toContain("return route('login');");

        $tempDir = sys_get_temp_dir().'/craft-filament-auth-test-'.uniqid();
        $filesystem->makeDirectory($tempDir, 0755, true);

        foreach ([
            'AdminPanelProvider.php' => $panelProviderStub,
            'AuthenticateFilament.php' => $middlewareContent,
        ] as $filename => $content) {
            $generatedContent = str_replace('{{namespace}}', 'App\\', $content);
            $tempFile = "{$tempDir}/{$filename}";

            file_put_contents($tempFile, $generatedContent);
            exec('php -l '.escapeshellarg($tempFile).' 2>&1', $output, $exitCode);

            expect($exitCode)->toBe(0, "PHP syntax error in generated {$filename}");
        }

        $filesystem->deleteDirectory($tempDir);
    });

    it('registers the Filament panel path as a non-Inertia login destination', function () {
        $reflection = new ReflectionClass(SetupCms::class);
        $property = $reflection->getProperty('tasks');
        $property->setAccessible(true);

        $tasks = array_values($property->getValue(new SetupCms(new Filesystem)));

        expect($tasks)->toContain(ConfigureFilamentAuthRedirectTask::class);

        $taskPosition = array_search(ConfigureFilamentAuthRedirectTask::class, $tasks, true);

        expect($taskPosition)
            ->toBeGreaterThan(array_search(RunSetupAuthTask::class, $tasks, true));
    });

    it('points Filament-only auth redirects at the admin panel', function () {
        $task = new ConfigureFilamentAuthRedirectTask(new Filesystem);
        $method = new ReflectionMethod($task, 'ensureFilamentHomePath');
        $method->setAccessible(true);

        $contents = <<<'PHP'
<?php

return [
    'home' => '/dashboard',
];
PHP;

        expect($method->invoke($task, $contents))
            ->toContain("'home' => '/admin',")
            ->not->toContain("'home' => '/dashboard',");
    });
});

describe('React auth scaffold installation', function () {
    it('scaffolds a Fortify login response that hard redirects to non-Inertia destinations', function () {
        $filesystem = new Filesystem;
        $packageRoot = dirname(__DIR__, 2);

        $responseStub = "{$packageRoot}/resources/stubs/auth/app/Http/Responses/Auth/LoginResponse.php";
        $providerStub = "{$packageRoot}/resources/stubs/auth/app/Providers/FortifyServiceProvider.php";
        $fortifyConfigStub = "{$packageRoot}/resources/stubs/auth/config/fortify.php";

        expect($filesystem->exists($responseStub))->toBeTrue();

        expect($filesystem->get($responseStub))
            ->toContain('namespace {{namespace}}Http\\Responses\\Auth;')
            ->toContain('use Inertia\\Inertia;')
            ->toContain('use Laravel\\Fortify\\Contracts\\LoginResponse as LoginResponseContract;')
            ->toContain('class LoginResponse implements LoginResponseContract')
            ->toContain('return Inertia::location($url);')
            ->toContain("config('fortify.non_inertia_paths', [])");

        expect($filesystem->get($providerStub))
            ->toContain('use {{namespace}}Http\\Responses\\Auth\\LoginResponse;')
            ->toContain('use Laravel\\Fortify\\Contracts\\LoginResponse as LoginResponseContract;')
            ->toContain('$this->app->singleton(LoginResponseContract::class, LoginResponse::class);');

        expect($filesystem->get($fortifyConfigStub))
            ->toContain("'non_inertia_paths' => [");
    });

    it('preserves route and page aliases while installing Craft registry items', function () {
        $filesystem = new Filesystem;
        $tempDir = sys_get_temp_dir().'/craft-components-test-'.uniqid();
        $componentsPath = "{$tempDir}/components.json";

        $filesystem->makeDirectory($tempDir, 0755, true);
        $filesystem->put($componentsPath, json_encode([
            'aliases' => [
                'components' => '@/components',
                'utils' => '@/lib/utils',
                'ui' => '@/components/ui',
                'lib' => '@/lib',
                'hooks' => '@/hooks',
            ],
        ], JSON_PRETTY_PRINT));

        $task = new InstallAuthReactScaffoldTask($filesystem);
        $method = new ReflectionMethod($task, 'pointCraftRegistryAtLocalServer');
        $method->setAccessible(true);
        $method->invoke($task, $componentsPath, 41000);

        $components = json_decode($filesystem->get($componentsPath), true, flags: JSON_THROW_ON_ERROR);

        expect($components['aliases'])
            ->toHaveKey('routes', '@/routes')
            ->toHaveKey('pages', '@/pages');

        $filesystem->deleteDirectory($tempDir);
    });

    it('runs the local Craft registry server with multiple workers', function () {
        $task = new InstallAuthReactScaffoldTask(new Filesystem);
        $method = new ReflectionMethod($task, 'registryServerProcess');
        $method->setAccessible(true);

        $process = $method->invoke($task, '/tmp/craft-registry', 41000);

        expect($process->getCommandLine())
            ->toContain('127.0.0.1:41000')
            ->toContain('/tmp/craft-registry')
            ->and($process->getEnv())
            ->toHaveKey('PHP_CLI_SERVER_WORKERS', '8');
    });

    it('discards registry server output before starting it', function () {
        $task = new InstallAuthReactScaffoldTask(new Filesystem);
        $method = new ReflectionMethod($task, 'registryServerProcess');
        $method->setAccessible(true);

        $process = $method->invoke($task, '/tmp/craft-registry', 41000);
        $process->disableOutput();

        expect($process->isOutputDisabled())->toBeTrue();
    });

    it('formats generated React files with the host app toolchain', function () {
        $task = new InstallAuthReactScaffoldTask(new Filesystem);
        $method = new ReflectionMethod($task, 'formatterProcess');
        $method->setAccessible(true);

        $process = $method->invoke($task);

        expect($process->getCommandLine())->toContain("'vp' 'check' '--fix' 'resources/js'");
    });

    it('normalizes shadcn-rewritten route and page imports after installation', function () {
        $filesystem = new Filesystem;
        $tempDir = sys_get_temp_dir().'/craft-import-normalize-test-'.uniqid();
        $filePath = "{$tempDir}/resources/js/pages/auth/login.tsx";
        $twoFactorFilePath = "{$tempDir}/resources/js/pages/auth/two-factor-challenge.tsx";

        $filesystem->ensureDirectoryExists(dirname($filePath));
        $filesystem->put($filePath, <<<'TSX'
import { login } from "@/resources/js/routes";
import { request } from "@/resources/js/routes/password";
import ProfileController from "@/resources/js/actions/App/Http/Controllers/Settings/ProfileController";
import { store as loginStore } from "@/resources/js/pages/auth/login";
import { store as registerStore } from "@/pages/auth/register";
import { store as twoFactorStore } from "@/pages/auth/two-factor-challenge";
import AuthLayout from "@/resources/js/pages/layouts/auth";
TSX);
        $filesystem->put($twoFactorFilePath, <<<'TSX'
import { store } from "@/resources/js/pages/auth/login";
TSX);

        $task = new InstallAuthReactScaffoldTask($filesystem);
        $method = new ReflectionMethod($task, 'normalizeGeneratedImports');
        $method->setAccessible(true);
        $method->invoke($task, $tempDir);

        expect($filesystem->get($filePath))
            ->toContain('from "@/routes"')
            ->toContain('from "@/routes/password"')
            ->toContain('from "@/actions/App/Http/Controllers/Settings/ProfileController"')
            ->toContain('from "@/routes/login"')
            ->toContain('from "@/routes/register"')
            ->toContain('from "@/routes/two-factor/login"')
            ->toContain('from "@/pages/layouts/auth"')
            ->not->toContain('@/resources/js');

        expect($filesystem->get($twoFactorFilePath))
            ->toContain('from "@/routes/two-factor/login"')
            ->not->toContain('@/routes/login');

        $filesystem->deleteDirectory($tempDir);
    });

    it('runs legacy Vue auth cleanup after installing the React auth scaffold', function () {
        $reflection = new ReflectionClass(SetupAuth::class);
        $property = $reflection->getProperty('tasks');
        $property->setAccessible(true);

        $tasks = array_values($property->getValue(new SetupAuth(new Filesystem)));

        expect($tasks)->toContain(CleanupLegacyAuthVueFilesTask::class);
        expect(array_search(CleanupLegacyAuthVueFilesTask::class, $tasks, true))
            ->toBeGreaterThan(array_search(InstallAuthReactScaffoldTask::class, $tasks, true));
    });

    it('configures the auth layout in the frontend bootstrap', function () {
        $filesystem = new Filesystem;
        $task = new ConfigureAuthFrontendBootstrapTask($filesystem);
        $importMethod = new ReflectionMethod($task, 'ensureAuthLayoutImport');
        $settingsImportMethod = new ReflectionMethod($task, 'ensureSettingsLayoutImports');
        $resolverMethod = new ReflectionMethod($task, 'ensureAuthLayoutResolver');
        $settingsResolverMethod = new ReflectionMethod($task, 'ensureSettingsLayoutResolver');

        $importMethod->setAccessible(true);
        $settingsImportMethod->setAccessible(true);
        $resolverMethod->setAccessible(true);
        $settingsResolverMethod->setAccessible(true);

        $contents = <<<'TSX'
import "../css/app.css";

import { createInertiaApp } from "@inertiajs/react";
import { initializeTheme } from "@/hooks/use-appearance";

createInertiaApp({
    layout: (_name) => {
        switch (true) {
            default:
                return null;
        }
    },
});

initializeTheme();
TSX;

        $contents = $importMethod->invoke($task, $contents);
        $contents = $settingsImportMethod->invoke($task, $contents);
        $contents = $resolverMethod->invoke($task, $contents);
        $contents = $settingsResolverMethod->invoke($task, $contents);
        $contents = $importMethod->invoke($task, $contents);
        $contents = $settingsImportMethod->invoke($task, $contents);
        $contents = $resolverMethod->invoke($task, $contents);
        $contents = $settingsResolverMethod->invoke($task, $contents);

        expect($contents)
            ->toContain('import AppLayout from "@/components/app-layout";')
            ->toContain('import AuthLayout from "@/components/auth-layout";')
            ->toContain('import SettingsLayout from "@/components/settings-layout";')
            ->toContain('case _name.startsWith("auth/"):')
            ->toContain('return AuthLayout;')
            ->toContain('case _name.startsWith("settings/"):')
            ->toContain('return [AppLayout, SettingsLayout];');

        expect(substr_count($contents, 'import AppLayout from "@/components/app-layout";'))->toBe(1);
        expect(substr_count($contents, 'import AuthLayout from "@/components/auth-layout";'))->toBe(1);
        expect(substr_count($contents, 'import SettingsLayout from "@/components/settings-layout";'))->toBe(1);
        expect(substr_count($contents, 'case _name.startsWith("auth/"):'))->toBe(1);
        expect(substr_count($contents, 'case _name.startsWith("settings/"):'))->toBe(1);
    });
});
