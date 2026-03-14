<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    roles: Array,
    services: Array,
    allPermissions: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.roles[0]?.name || '',
    service_id: props.user.service_id || '',
    permissions: props.user.permissions?.map(p => p.name) || [],
});

const getCategoryLabel = (category) => {
    const labels = {
        'view': 'Visualisation',
        'create': 'Création',
        'update': 'Modification',
        'delete': 'Suppression',
        'manage': 'Gestion',
        'assign': 'Attribution',
        'close': 'Fermeture',
        'resolve': 'Résolution',
        'export': 'Export',
    };
    return labels[category] || category;
};

const submit = () => {
    form.patch(route('users.update', props.user.id));
};
</script>

<template>
    <Head :title="`Modifier ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Modifier l'utilisateur
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="Nom *" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="email" value="Email *" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="role" value="Rôle *" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Sélectionner un rôle</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="service_id" value="Service" />
                                <select
                                    id="service_id"
                                    v-model="form.service_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">-- Aucun service --</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">
                                        {{ service.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.service_id" />
                            </div>

                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <p class="text-sm text-gray-600 mb-4">
                                    Laissez les champs de mot de passe vides pour conserver le mot de passe actuel.
                                </p>

                                <div>
                                    <InputLabel for="password" value="Nouveau mot de passe" />
                                    <TextInput
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.password" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="password_confirmation" value="Confirmer le nouveau mot de passe" />
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                            </div>

                            <!-- Permissions Section -->
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <h3 class="text-base font-medium text-gray-900 mb-4">
                                    Gestion des permissions
                                </h3>

                                <!-- Role Permissions (Read-only) -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">
                                        Permissions héritées du rôle "{{ user.roles[0]?.name }}"
                                    </h4>
                                    <div v-if="user.roles[0]?.permissions?.length > 0" class="space-y-2">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                            <div 
                                                v-for="permission in user.roles[0].permissions" 
                                                :key="permission.id"
                                                class="flex items-center rounded-md border border-gray-200 bg-gray-50 p-3 opacity-60"
                                            >
                                                <input 
                                                    type="checkbox" 
                                                    :checked="true" 
                                                    disabled
                                                    class="h-4 w-4 rounded border-gray-300 text-gray-400"
                                                />
                                                <label class="ml-2 text-sm text-gray-500">
                                                    {{ permission.name.replace(/_/g, ' ') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-gray-500 italic">
                                        Aucune permission héritée du rôle
                                    </p>
                                </div>

                                <!-- Direct User Permissions (Editable) -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">
                                        Permissions directes (en plus des permissions du rôle)
                                    </h4>
                                    <p class="text-xs text-gray-500 mb-3">
                                        Les permissions cochées ci-dessous seront ajoutées aux permissions du rôle
                                    </p>
                                    <div v-for="(group, category) in allPermissions" :key="category" class="mb-4">
                                        <h5 class="text-xs font-semibold text-gray-600 mb-2 uppercase">
                                            {{ getCategoryLabel(category) }}
                                        </h5>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                            <label 
                                                v-for="permission in group.permissions" 
                                                :key="permission.id"
                                                class="flex items-center rounded-md border p-3 cursor-pointer hover:bg-gray-50"
                                            >
                                                <input 
                                                    v-model="form.permissions" 
                                                    type="checkbox" 
                                                    :value="permission.name"
                                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700">
                                                    {{ permission.name.replace(/_/g, ' ') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end space-x-4">
                                <SecondaryButton type="button" @click="$inertia.visit(route('users.index'))">
                                    Annuler
                                </SecondaryButton>
                                <PrimaryButton :disabled="form.processing">
                                    Enregistrer les modifications
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>