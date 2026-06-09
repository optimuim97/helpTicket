<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ roles: Array });

const deleteRole = (role) => {
    if (confirm(`Supprimer le rôle « ${role.name} » ?`)) {
        router.delete(route('roles.destroy', role.id));
    }
};
</script>

<template>
    <Head title="Rôles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Rôles</h1>
                    <p class="text-sm text-slate-500">Définissez les groupes et leurs permissions.</p>
                </div>
                <Link :href="route('roles.create')" class="btn-accent">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau rôle
                </Link>
            </div>
        </template>

        <section class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-3">Nom</th>
                            <th class="px-6 py-3">Utilisateurs</th>
                            <th class="px-6 py-3">Permissions</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="role in roles" :key="role.id" class="transition hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ role.name }}</td>
                            <td class="px-6 py-4">
                                <span class="badge bg-primary-100 text-primary-700">{{ role.users_count }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge bg-emerald-100 text-emerald-700">{{ role.permissions_count }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <Link :href="route('roles.edit', role.id)" class="font-medium text-primary hover:text-primary-700">Modifier</Link>
                                <button @click="deleteRole(role)" class="ml-4 font-medium text-red-600 hover:text-red-700">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
