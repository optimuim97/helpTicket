<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({ name: '', description: '', is_active: true });
const submit = () => form.post(route('services.store'));

const inputClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head title="Créer un service" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Nouveau service</h1>
                <p class="text-sm text-slate-500">Définissez un service interne.</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl">
            <form @submit.prevent="submit" class="card space-y-5 p-6">
                <div>
                    <InputLabel for="name" value="Nom du service *" />
                    <TextInput id="name" v-model="form.name" type="text" required class="mt-1.5" placeholder="Ex : Support Technique" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea id="description" v-model="form.description" rows="4" :class="inputClass" placeholder="Description du service…"></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
                    Service actif
                </label>
                <p class="-mt-3 text-xs text-slate-500">Les services inactifs ne peuvent pas être assignés à de nouveaux utilisateurs.</p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <SecondaryButton type="button" @click="$inertia.visit(route('services.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">{{ form.processing ? 'Création…' : 'Créer le service' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
