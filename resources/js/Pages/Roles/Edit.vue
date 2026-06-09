<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    role: Object,
    allPermissions: Object,
    rolePermissions: Array,
});

const form = useForm({
    name: props.role.name,
    permissions: props.rolePermissions || [],
});

const submit = () => form.put(route('roles.update', props.role.id));

const categoryLabels = {
    view: 'Visualisation', create: 'Création', update: 'Modification', delete: 'Suppression',
    manage: 'Gestion', assign: 'Attribution', close: 'Fermeture', resolve: 'Résolution', export: 'Export',
};
const getCategoryLabel = (c) => categoryLabels[c] || c;

const initials = (name) => (name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
</script>

<template>
    <Head :title="`Modifier ${role.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Édition rôle</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ role.name }}</h1>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Identité</h2>
                    <div class="mt-5">
                        <InputLabel for="name" value="Nom du rôle *" />
                        <TextInput id="name" v-model="form.name" type="text" required class="mt-1.5" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Permissions</h2>
                    <div v-for="(group, category) in allPermissions" :key="category" class="mt-5">
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-primary-700">{{ getCategoryLabel(category) }}</h4>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="permission in group"
                                :key="permission.id"
                                class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm transition hover:border-primary-200 hover:bg-primary-50"
                            >
                                <input
                                    v-model="form.permissions"
                                    type="checkbox"
                                    :value="permission.name"
                                    class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                                />
                                <span class="text-slate-700">{{ permission.name.replace(/_/g, ' ') }}</span>
                            </label>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('roles.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}</PrimaryButton>
                </div>
            </form>

            <section class="card p-6">
                <h3 class="text-base font-semibold text-slate-800">
                    Utilisateurs avec ce rôle
                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ role.users?.length || 0 }}
                    </span>
                </h3>
                <div v-if="role.users && role.users.length > 0" class="mt-4 space-y-2">
                    <div
                        v-for="user in role.users"
                        :key="user.id"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 p-3"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                            {{ initials(user.name) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ user.name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ user.email }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-slate-500">Aucun utilisateur avec ce rôle.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
