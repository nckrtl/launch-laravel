<script setup lang="ts">
import { LoginPage, type LoginForm } from "@hardimpactdev/craft-ui";
import { router } from "@inertiajs/vue3";

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    devUser?: { name: string; email: string } | null;
}>();

const form = useForm<LoginForm>({
    email: "",
    password: "",
    remember: false,
});

const handleSubmit = (data: LoginForm) => {
    form.email = data.email;
    form.password = data.password;
    form.remember = data.remember;

    form.submit("/login", {
        onFinish: () => form.reset("password"),
    });
};

function devLogin() {
    router.post("/laravel-login-link-login");
}
</script>

<template>
    <Head title="Log in" />
    <LoginPage
        :status="status"
        :can-reset-password="canResetPassword"
        :can-register="canRegister"
        :errors="form.errors"
        :processing="form.processing"
        @submit="handleSubmit"
    />
    <div v-if="devUser" class="fixed top-4 left-1/2 z-50 -translate-x-1/2">
        <button
            @click="devLogin"
            class="rounded-full bg-amber-100 px-4 py-2 text-sm font-medium text-amber-800 shadow-lg transition hover:bg-amber-200"
        >
            Login as {{ devUser.name }}
        </button>
    </div>
</template>
