<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    users: Object,
    roles: Array,
});

const deleteUser = (user) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.name} ?`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Gestion des utilisateurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gestion des utilisateurs
                </h2>
                <Link
                    :href="route('users.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-900"
                >
                    Créer un utilisateur
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div v-if="users.data.length === 0" class="p-6 text-center text-gray-500">
                        Aucun utilisateur trouvé
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nom
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Rôle(s)
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Service
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Créé le
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ user.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ user.email }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role.id"
                                                class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <span v-if="user.service" class="text-gray-900">
                                            {{ user.service.name }}
                                        </span>
                                        <span v-else class="italic text-gray-400">
                                            Aucun
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="flex space-x-3">
                                            <Link
                                                :href="route('users.edit', user.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Modifier
                                            </Link>
                                            <button
                                                @click="deleteUser(user)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links.length > 3" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Affichage de
                                <span class="font-medium">{{ users.from }}</span>
                                à
                                <span class="font-medium">{{ users.to }}</span>
                                sur
                                <span class="font-medium">{{ users.total }}</span>
                                résultats
                            </div>

                            <div class="flex space-x-2">
                                <Link
                                    v-for="(link, index) in users.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'rounded border px-3 py-1 text-sm',
                                        link.active
                                            ? 'border-blue-600 bg-blue-600 text-white'
                                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                        !link.url ? 'cursor-not-allowed opacity-50' : '',
                                    ]"
                                    :disabled="!link.url"
                                    :v-html="link.label"
                                >
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>