<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

defineProps({
    settings: Object,
});

const form = useForm({
    settings: {},
});

const logoForm = useForm({
    logo: null,
});

const logoPreview = ref(null);

const updateSettings = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
    });
};

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        logoForm.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const uploadLogo = () => {
    logoForm.post(route('settings.upload-logo'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            logoForm.reset();
            logoPreview.value = null;
        },
    });
};

const getGroupTitle = (group) => {
    const titles = {
        branding: 'Image de marque',
        general: 'Général',
        notifications: 'Notifications',
        email: 'Email',
    };
    return titles[group] || group;
};

const getInputType = (type) => {
    if (type === 'boolean') return 'checkbox';
    if (type === 'integer') return 'number';
    return 'text';
};
</script>

<template>
    <Head title="Paramètres" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Paramètres de l'application
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <!-- Logo Upload Section -->
                        <div class="mb-8 border-b pb-8">
                            <h3 class="mb-4 text-lg font-semibold">Logo de l'application</h3>
                            <div class="flex items-start gap-6">
                                <div class="flex-shrink-0">
                                    <img 
                                        v-if="logoPreview" 
                                        :src="logoPreview" 
                                        alt="Preview" 
                                        class="h-24 w-auto rounded-lg border"
                                    />
                                    <img 
                                        v-else-if="$page.props.appSettings.app_logo" 
                                        :src="`/storage/${$page.props.appSettings.app_logo}`" 
                                        alt="Logo actuel" 
                                        class="h-24 w-auto rounded-lg border"
                                    />
                                    <div v-else class="flex h-24 w-24 items-center justify-center rounded-lg border bg-gray-100">
                                        <span class="text-gray-400">Aucun logo</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <input 
                                        type="file" 
                                        @change="handleLogoChange" 
                                        accept="image/*"
                                        class="mb-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p class="mb-2 text-sm text-gray-500">
                                        Formats acceptés: JPG, PNG. Taille max: 3MB
                                    </p>
                                    <button
                                        @click="uploadLogo"
                                        :disabled="!logoForm.logo || logoForm.processing"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                    >
                                        {{ logoForm.processing ? 'Téléchargement...' : 'Télécharger le logo' }}
                                    </button>
                                    <p v-if="logoForm.errors.logo" class="mt-2 text-sm text-red-600">
                                        {{ logoForm.errors.logo }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Form -->
                        <form @submit.prevent="updateSettings">
                            <div v-for="(groupSettings, group) in settings" :key="group" class="mb-8">
                                <h3 class="mb-4 text-lg font-semibold">{{ getGroupTitle(group) }}</h3>
                                
                                <div class="space-y-4">
                                    <div 
                                        v-for="setting in groupSettings" 
                                        :key="setting.key"
                                        class="rounded-lg border p-4"
                                    >
                                        <!-- Skip logo setting as it's handled above -->
                                        <template v-if="setting.key !== 'app_logo'">
                                            <label :for="setting.key" class="mb-1 block text-sm font-medium text-gray-700">
                                                {{ setting.label }}
                                            </label>
                                            <p v-if="setting.description" class="mb-2 text-sm text-gray-500">
                                                {{ setting.description }}
                                            </p>
                                            
                                            <!-- Boolean (checkbox) -->
                                            <div v-if="setting.type === 'boolean'" class="flex items-center">
                                                <input
                                                    :id="setting.key"
                                                    v-model="form.settings[setting.key]"
                                                    type="checkbox"
                                                    :checked="setting.value"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label :for="setting.key" class="ml-2 text-sm text-gray-600">
                                                    Activé
                                                </label>
                                            </div>
                                            
                                            <!-- Text/Number input -->
                                            <input
                                                v-else
                                                :id="setting.key"
                                                v-model="form.settings[setting.key]"
                                                :type="getInputType(setting.type)"
                                                :value="setting.value"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                    Paramètres enregistrés avec succès.
                                </p>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer les paramètres' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
