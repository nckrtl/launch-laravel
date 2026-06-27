<script setup lang="ts">
import {
    Badge,
    Button,
    HeadingSmall,
    SettingsLayout,
} from "@hardimpactdev/craft-ui";
import AppLayout from "@/layouts/AppLayout.vue";
import { ShieldBan, ShieldCheck, LoaderCircle } from "lucide-vue-next";
import { onUnmounted, ref } from "vue";
import TwoFactorRecoveryCodes from "@/components/TwoFactorRecoveryCodes.vue";
import TwoFactorSetupModal from "@/components/TwoFactorSetupModal.vue";
import { useTwoFactorAuth } from "@/composables/useTwoFactorAuth";
import { type BreadcrumbItem } from "@/types";

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Two-Factor Authentication",
        href: "/settings/two-factor",
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);
const isEnabling = ref(false);
const isDisabling = ref(false);

const enableTwoFactor = async () => {
    isEnabling.value = true;
    try {
        const response = await fetch("/user/two-factor-authentication", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });
        if (response.ok) {
            showSetupModal.value = true;
        }
    } finally {
        isEnabling.value = false;
    }
};

const disableTwoFactor = async () => {
    isDisabling.value = true;
    try {
        const response = await fetch("/user/two-factor-authentication", {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });
        if (response.ok) {
            window.location.reload();
        }
    } finally {
        isDisabling.value = false;
    }
};

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Two-Factor Authentication" />

        <h1 class="sr-only">Two-Factor Authentication Settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Two-Factor Authentication"
                    description="Manage your two-factor authentication settings"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="destructive">Disabled</Badge>

                    <p class="text-muted-foreground">
                        When you enable two-factor authentication, you will be
                        prompted for a secure pin during login. This pin can be
                        retrieved from a TOTP-supported application on your
                        phone.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck class="size-4" />
                            Continue Setup
                        </Button>
                        <Button
                            v-else
                            @click="enableTwoFactor"
                            :disabled="isEnabling"
                        >
                            <LoaderCircle
                                v-if="isEnabling"
                                class="size-4 animate-spin"
                            />
                            <ShieldCheck v-else class="size-4" />
                            Enable 2FA
                        </Button>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="default">Enabled</Badge>

                    <p class="text-muted-foreground">
                        With two-factor authentication enabled, you will be
                        prompted for a secure, random pin during login, which
                        you can retrieve from the TOTP-supported application on
                        your phone.
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <Button
                            variant="destructive"
                            @click="disableTwoFactor"
                            :disabled="isDisabling"
                        >
                            <LoaderCircle
                                v-if="isDisabling"
                                class="size-4 animate-spin"
                            />
                            <ShieldBan v-else class="size-4" />
                            Disable 2FA
                        </Button>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
