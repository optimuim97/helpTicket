<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ services: Array });

const deleteService = (service) => {
    if (confirm(`Supprimer le service « ${service.name} » ? Les utilisateurs assignés seront dissociés.`)) {
        router.delete(route('services.destroy', service.id));
    }
};
</script>

<template>
    <Head title="Services" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Services</h1>
                    <p class="text-sm text-slate-500">Organisation interne et affectations.</p>
                </div>
                <Link :href="route('services.create')" class="btn-accent">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau service
                </Link>
            </div>
        </template>

        <section class="card overflow-hidden">
            <div v-if="!services?.length" class="px-6 py-16 text-center text-sm text-slate-500">
                Aucun service.
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-3">Nom</th>
                            <th class="px-6 py-3">Description</th>
                            <th class="px-6 py-3">Utilisateurs</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="service in services" :key="service.id" class="transition hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ service.name }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ service.description || '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="badge bg-primary-100 text-primary-700">{{ service.users_count }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['badge', service.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                                    {{ service.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <Link :href="route('services.edit', service.id)" class="font-medium text-primary hover:text-primary-700">Modifier</Link>
                                <button @click="deleteService(service)" class="ml-4 font-medium text-red-600 hover:text-red-700">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
