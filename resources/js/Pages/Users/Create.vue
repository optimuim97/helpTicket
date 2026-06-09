<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    roles: Array,
    services: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    service_id: '',
});

const submit = () => form.post(route('users.store'));

const selectClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head title="Créer un utilisateur" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Créer un utilisateur</h1>
                <p class="text-sm text-slate-500">Renseignez les informations du compte.</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Identité</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <InputLabel for="name" value="Nom complet *" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1.5" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="email" value="Email *" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Affectation</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
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
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <InputLabel for="password" value="Mot de passe *" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <div>
                            <InputLabel for="password_confirmation" value="Confirmer *" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1.5" required />
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('users.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Créer l'utilisateur</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
