@php($section = $section ?? 'setup')

@if ($section === 'setup')
    <style>
        .fi-two-factor-setup {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding-top: 0.5rem;
            padding-bottom: 0.25rem;
        }

        .fi-two-factor-field {
            display: grid;
            gap: 0.5rem;
        }

        .fi-two-factor-field .fi-fo-field-label-content {
            font-weight: 500;
        }

        .fi-two-factor-code-block {
            display: block;
            overflow-x: auto;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.5rem;
            background: rgb(249 250 251);
            padding: 1rem 1.25rem;
            color: rgb(17 24 39);
            font-size: 0.9375rem;
            line-height: 1.75rem;
            white-space: pre;
        }

        .fi-two-factor-code-block code {
            font: inherit;
        }

        .dark .fi-two-factor-code-block {
            border-color: rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.08);
            color: rgb(255 255 255);
        }
    </style>

    <div class="fi-two-factor-setup">
        @if (filled($user->two_factor_secret))
            <div class="inline-block rounded-lg bg-white p-3">
                {!! $user->twoFactorQrCodeSvg() !!}
            </div>
        @endif
    </div>
@endif

@if ($section === 'recovery')
    @if (filled($user->two_factor_recovery_codes))
        <div class="fi-two-factor-field">
            <div class="fi-fo-field-label-ctn">
                <p class="fi-fo-field-label">
                    <span class="fi-fo-field-label-content">
                        Recovery codes
                    </span>
                </p>
            </div>

            <div class="fi-fo-field-content-col">
                <pre class="fi-two-factor-code-block"><code>{{ implode(PHP_EOL, $user->recoveryCodes()) }}</code></pre>
            </div>
        </div>
    @endif
@endif
