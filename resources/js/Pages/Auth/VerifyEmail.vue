<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ status: { type: String } });

const form = useForm({});

const submit = () => form.post(route('verification.send'));

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Vérification d'e-mail" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Vérifiez votre e-mail 📧</h1>
            <p class="mt-2 text-sm text-slate-500">
                Merci pour votre inscription ! Cliquez sur le lien que nous venons de vous envoyer pour confirmer votre adresse.
                Vous pouvez en demander un nouveau si nécessaire.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-6 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
        >
            Un nouveau lien de vérification vient d'être envoyé à votre adresse e-mail.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <button
                type="submit"
                class="btn-primary w-full py-2.5 text-base"
                :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Envoi…' : 'Renvoyer le lien' }}
            </button>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="btn-ghost w-full py-2.5"
            >
                Se déconnecter
            </Link>
        </form>
    </GuestLayout>
</template>
