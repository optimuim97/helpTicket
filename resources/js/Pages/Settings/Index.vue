<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

defineProps({ settings: Object });

const page = usePage();
const form = useForm({ settings: {} });
const logoForm = useForm({ logo: null });
const logoPreview = ref(null);

const updateSettings = () => form.post(route('settings.update'), { preserveScroll: true });

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        logoForm.logo = file;
        const reader = new FileReader();
        reader.onload = (ev) => { logoPreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
};

const uploadLogo = () => {
    logoForm.post(route('settings.upload-logo'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { logoForm.reset(); logoPreview.value = null; },
    });
};

const groupTitles = {
    branding: 'Image de marque',
    general: 'Général',
    notifications: 'Notifications',
    email: 'Email',
};
const getGroupTitle = (group) => groupTitles[group] || group;
const getInputType = (type) => type === 'boolean' ? 'checkbox' : (type === 'integer' ? 'number' : 'text');

const inputClass = 'block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head title="Paramètres" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Paramètres</h1>
                <p class="text-sm text-slate-500">Personnalisez votre application.</p>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Logo -->
            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-800">Logo de l'application</h2>
                <p class="mt-1 text-xs text-slate-500">Formats acceptés : JPG, PNG. Taille max : 3 MB.</p>

                <div class="mt-5 flex flex-col items-start gap-6 sm:flex-row">
                    <div class="shrink-0">
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            alt="Aperçu"
                            class="h-24 w-auto rounded-xl border border-slate-200 object-contain bg-white p-2"
                        />
                        <img
                            v-else-if="page.props.appSettings?.app_logo"
                            :src="`/storage/${page.props.appSettings.app_logo}`"
                            alt="Logo actuel"
                            class="h-24 w-auto rounded-xl border border-slate-200 object-contain bg-white p-2"
                        />
                        <div v-else class="flex h-24 w-24 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-400">
                            Aucun logo
                        </div>
                    </div>
                    <div class="flex-1">
                        <input
                            type="file"
                            @change="handleLogoChange"
                            accept="image/*"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
                        />
                        <button
                            @click="uploadLogo"
                            :disabled="!logoForm.logo || logoForm.processing"
                            class="btn-primary mt-3"
                        >
                            {{ logoForm.processing ? 'Téléchargement…' : 'Téléverser le logo' }}
                        </button>
                        <p v-if="logoForm.errors.logo" class="mt-2 text-sm text-red-600">{{ logoForm.errors.logo }}</p>
                    </div>
                </div>
            </section>

            <!-- Settings groups -->
            <form @submit.prevent="updateSettings" class="space-y-6">
                <section v-for="(groupSettings, group) in settings" :key="group" class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">{{ getGroupTitle(group) }}</h2>

                    <div class="mt-5 space-y-4">
                        <div
                            v-for="setting in groupSettings"
                            :key="setting.key"
                            v-show="setting.key !== 'app_logo'"
                            class="rounded-xl border border-slate-200 p-4"
                        >
                            <label :for="setting.key" class="block text-sm font-medium text-slate-700">
                                {{ setting.label }}
                            </label>
                            <p v-if="setting.description" class="mt-0.5 text-xs text-slate-500">{{ setting.description }}</p>

                            <div v-if="setting.type === 'boolean'" class="mt-3 flex items-center gap-2">
                                <input
                                    :id="setting.key"
                                    v-model="form.settings[setting.key]"
                                    type="checkbox"
                                    :checked="setting.value"
                                    class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                                />
                                <label :for="setting.key" class="text-sm text-slate-600">Activé</label>
                            </div>

                            <input
                                v-else
                                :id="setting.key"
                                v-model="form.settings[setting.key]"
                                :type="getInputType(setting.type)"
                                :value="setting.value"
                                :class="['mt-3', inputClass]"
                            />
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-4">
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600">✓ Paramètres enregistrés</p>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
