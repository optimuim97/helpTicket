<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ service: Object });

const form = useForm({
    name: props.service.name,
    description: props.service.description || '',
    is_active: props.service.is_active,
});

const submit = () => form.put(route('services.update', props.service.id));

const inputClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';

const initials = (name) => (name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
</script>

<template>
    <Head :title="`Modifier ${service.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Édition service</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ service.name }}</h1>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <form @submit.prevent="submit" class="card space-y-5 p-6">
                <div>
                    <InputLabel for="name" value="Nom du service *" />
                    <TextInput id="name" v-model="form.name" type="text" required class="mt-1.5" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea id="description" v-model="form.description" rows="4" :class="inputClass"></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
                    Service actif
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <SecondaryButton type="button" @click="$inertia.visit(route('services.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}</PrimaryButton>
                </div>
            </form>

            <section class="card p-6">
                <h3 class="text-base font-semibold text-slate-800">
                    Utilisateurs dans ce service
                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ service.users?.length || 0 }}
                    </span>
                </h3>
                <div v-if="service.users && service.users.length > 0" class="mt-4 space-y-2">
                    <div
                        v-for="user in service.users"
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
                <p v-else class="mt-3 text-sm text-slate-500">Aucun utilisateur assigné à ce service.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
