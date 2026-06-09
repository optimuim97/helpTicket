<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({ permissions: Object });

const form = useForm({ name: '', permissions: [] });
const submit = () => form.post(route('roles.store'));

const categoryLabels = {
    view: 'Visualisation', create: 'Création', update: 'Modification', delete: 'Suppression',
    manage: 'Gestion', assign: 'Attribution', close: 'Fermeture', resolve: 'Résolution', export: 'Export',
};
const getCategoryLabel = (c) => categoryLabels[c] || c;
</script>

<template>
    <Head title="Créer un rôle" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Nouveau rôle</h1>
                <p class="text-sm text-slate-500">Définissez le nom et les permissions associées.</p>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Identité</h2>
                    <div class="mt-5">
                        <InputLabel for="name" value="Nom du rôle *" />
                        <TextInput id="name" v-model="form.name" type="text" required class="mt-1.5" placeholder="Ex : Manager" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Permissions</h2>
                    <div v-for="(group, category) in permissions" :key="category" class="mt-5">
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
                    <PrimaryButton :disabled="form.processing">{{ form.processing ? 'Création…' : 'Créer le rôle' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
