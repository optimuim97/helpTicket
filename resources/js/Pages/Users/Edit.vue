<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    roles: Array,
    services: Array,
    allPermissions: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.roles[0]?.name || '',
    service_id: props.user.service_id || '',
    permissions: props.user.permissions?.map(p => p.name) || [],
});

const categoryLabels = {
    view: 'Visualisation', create: 'Création', update: 'Modification', delete: 'Suppression',
    manage: 'Gestion', assign: 'Attribution', close: 'Fermeture', resolve: 'Résolution', export: 'Export',
};
const getCategoryLabel = (c) => categoryLabels[c] || c;

const submit = () => form.patch(route('users.update', props.user.id));

const selectClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';

const initials = (props.user.name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
</script>

<template>
    <Head :title="`Modifier ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-base font-semibold text-white">
                    {{ initials }}
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Édition utilisateur</p>
                    <h1 class="text-2xl font-bold text-slate-900">{{ user.name }}</h1>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Identité</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <InputLabel for="name" value="Nom complet *" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1.5" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email *" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <InputLabel for="role" value="Rôle *" />
                            <select id="role" v-model="form.role" :class="selectClass" required>
                                <option value="">Sélectionner</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.role" />
                        </div>
                        <div>
                            <InputLabel for="service_id" value="Service" />
                            <select id="service_id" v-model="form.service_id" :class="selectClass">
                                <option value="">— Aucun —</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.service_id" />
                        </div>
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Mot de passe</h2>
                    <p class="mt-1 text-xs text-slate-500">Laissez vide pour conserver le mot de passe actuel.</p>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <InputLabel for="password" value="Nouveau mot de passe" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1.5" autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Confirmer" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1.5" autocomplete="new-password" />
                        </div>
                    </div>
                </section>

                <!-- Permissions -->
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Permissions</h2>

                    <div class="mt-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Héritées du rôle « {{ user.roles[0]?.name }} »
                        </h3>
                        <div v-if="user.roles[0]?.permissions?.length > 0" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="permission in user.roles[0].permissions"
                                :key="permission.id"
                                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
                            >
                                <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ permission.name.replace(/_/g, ' ') }}
                            </div>
                        </div>
                        <p v-else class="mt-2 text-sm italic text-slate-400">Aucune permission héritée.</p>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Permissions directes</h3>
                        <p class="mt-1 text-xs text-slate-500">Ces permissions s'ajoutent à celles du rôle.</p>

                        <div v-for="(group, category) in allPermissions" :key="category" class="mt-5">
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-primary-700">{{ getCategoryLabel(category) }}</h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <label
                                    v-for="permission in group.permissions"
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
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('users.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Enregistrer</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
