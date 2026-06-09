<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-semibold text-slate-900">Informations du profil</h2>
            <p class="mt-1 text-sm text-slate-500">Mettez à jour votre nom et votre adresse e-mail.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 max-w-xl space-y-5">
            <div>
                <InputLabel for="name" value="Nom" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Adresse e-mail" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-lg border border-accent-100 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                Votre adresse n'est pas vérifiée.
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="ml-1 font-medium text-primary underline hover:text-primary-700"
                >
                    Renvoyer le lien de vérification
                </Link>
                <p
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-medium text-emerald-700"
                >
                    Un nouveau lien vient d'être envoyé à votre adresse e-mail.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Enregistrer</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600">✓ Enregistré</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
