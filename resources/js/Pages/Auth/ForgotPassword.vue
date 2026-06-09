<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ status: { type: String } });

const form = useForm({ email: '' });

const submit = () => form.post(route('password.email'));
</script>

<template>
    <GuestLayout>
        <Head title="Mot de passe oublié" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Mot de passe oublié ?</h1>
            <p class="mt-2 text-sm text-slate-500">
                Indiquez votre adresse e-mail, nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Adresse e-mail" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5"
                    v-model="form.email"
                    placeholder="nom@paa.ci"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <button
                type="submit"
                class="btn-primary w-full py-2.5 text-base"
                :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Envoi…' : 'Envoyer le lien' }}
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500">
            <Link :href="route('login')" class="font-medium text-primary hover:text-primary-700">
                ← Retour à la connexion
            </Link>
        </p>
    </GuestLayout>
</template>
