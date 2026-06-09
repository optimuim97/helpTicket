<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Créer un compte" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Créer un compte</h1>
            <p class="mt-2 text-sm text-slate-500">Quelques informations et c'est parti.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="Nom complet" />
                <TextInput id="name" type="text" class="mt-1.5" v-model="form.name" placeholder="Jean Dupont" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Adresse e-mail" />
                <TextInput id="email" type="email" class="mt-1.5" v-model="form.email" placeholder="nom@paa.ci" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Mot de passe" />
                <TextInput id="password" type="password" class="mt-1.5" v-model="form.password" placeholder="••••••••" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                <TextInput id="password_confirmation" type="password" class="mt-1.5" v-model="form.password_confirmation" placeholder="••••••••" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <button
                type="submit"
                class="btn-primary w-full py-2.5 text-base"
                :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Création…' : 'Créer mon compte' }}
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500">
            Déjà inscrit ?
            <Link :href="route('login')" class="font-medium text-primary hover:text-primary-700">Se connecter</Link>
        </p>
    </GuestLayout>
</template>
