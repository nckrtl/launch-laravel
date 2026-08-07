<div
    class="space-y-4"
    x-data="{
        name: '',
        error: '',
        loading: false,
        async registerPasskey() {
            this.error = '';

            if (! this.name.trim()) {
                this.error = 'Enter a passkey name.';

                return;
            }

            if (! window.PublicKeyCredential || ! navigator.credentials?.create) {
                this.error = 'Passkeys are not supported in this browser.';

                return;
            }

            this.loading = true;

            try {
                const optionsResponse = await fetch(@js(route('passkey.registration-options')), {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (! optionsResponse.ok) {
                    throw new Error('Unable to start passkey registration.');
                }

                const { options } = await optionsResponse.json();
                const credential = await navigator.credentials.create({
                    publicKey: this.prepareCreationOptions(options),
                });

                const storeResponse = await fetch(@js(route('passkey.store')), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content || '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name: this.name,
                        credential: this.serializeCredential(credential),
                    }),
                });

                if (! storeResponse.ok) {
                    throw new Error('Unable to store passkey.');
                }

                window.location.reload();
            } catch (error) {
                this.error = error instanceof Error ? error.message : 'Unable to register passkey.';
            } finally {
                this.loading = false;
            }
        },
        async deletePasskey(url) {
            this.error = '';
            this.loading = true;

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content || '',
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error('Unable to delete passkey.');
                }

                window.location.reload();
            } catch (error) {
                this.error = error instanceof Error ? error.message : 'Unable to delete passkey.';
            } finally {
                this.loading = false;
            }
        },
        prepareCreationOptions(options) {
            options.challenge = this.base64UrlToBuffer(options.challenge);
            options.user.id = this.base64UrlToBuffer(options.user.id);
            options.excludeCredentials = (options.excludeCredentials || []).map((credential) => ({
                ...credential,
                id: this.base64UrlToBuffer(credential.id),
            }));

            return options;
        },
        serializeCredential(credential) {
            return {
                id: credential.id,
                rawId: this.bufferToBase64Url(credential.rawId),
                response: {
                    attestationObject: this.bufferToBase64Url(credential.response.attestationObject),
                    clientDataJSON: this.bufferToBase64Url(credential.response.clientDataJSON),
                },
                type: credential.type,
                clientExtensionResults: credential.getClientExtensionResults(),
            };
        },
        base64UrlToBuffer(value) {
            const base64 = value.replace(/-/g, '+').replace(/_/g, '/');
            const padded = base64.padEnd(base64.length + ((4 - (base64.length % 4)) % 4), '=');
            const binary = atob(padded);
            const bytes = new Uint8Array(binary.length);

            for (let index = 0; index < binary.length; index++) {
                bytes[index] = binary.charCodeAt(index);
            }

            return bytes.buffer;
        },
        bufferToBase64Url(buffer) {
            const bytes = new Uint8Array(buffer);
            let binary = '';

            for (const byte of bytes) {
                binary += String.fromCharCode(byte);
            }

            return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        },
    }"
>
    <div class="space-y-2">
        @forelse ($user->passkeys as $passkey)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 dark:border-white/10">
                <div>
                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $passkey->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Added {{ $passkey->created_at?->diffForHumans() }}
                    </p>
                </div>

                <button
                    type="button"
                    class="text-sm font-medium text-danger-600 hover:text-danger-500"
                    x-on:click="deletePasskey(@js(route('passkey.destroy', $passkey)))"
                    x-bind:disabled="loading"
                >
                    Remove
                </button>
            </div>
        @empty
            <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                No passkeys have been registered yet.
            </p>
        @endforelse
    </div>

    <form class="space-y-3" x-on:submit.prevent="registerPasskey">
        <label class="block text-sm font-medium text-gray-950 dark:text-white" for="filament-passkey-name">
            Passkey name
        </label>

        <input
            id="filament-passkey-name"
            type="text"
            class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-white/10 dark:bg-white/5"
            placeholder="MacBook Pro, iPhone"
            x-model="name"
        />

        <p class="text-sm text-danger-600" x-show="error" x-text="error"></p>

        <button
            type="submit"
            class="fi-btn fi-color-primary inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold"
            x-bind:disabled="loading"
        >
            Add passkey
        </button>
    </form>
</div>
