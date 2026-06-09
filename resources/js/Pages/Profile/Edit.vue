<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = computed(() => usePage().props.auth.user);
const initials = computed(() => (user.value.name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase());
</script>

<template>
    <Head title="Mon profil" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-lg font-semibold text-white">
                    {{ initials }}
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ user.name }}</h1>
                    <p class="text-sm text-slate-500">{{ user.email }}</p>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <section class="card p-6 sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </section>

            <section class="card p-6 sm:p-8">
                <UpdatePasswordForm />
            </section>

            <section class="card border border-red-100 p-6 sm:p-8">
                <DeleteUserForm />
            </section>
        </div>
    </AuthenticatedLayout>
</template>
