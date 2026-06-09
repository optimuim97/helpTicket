<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import Skeleton from '@/Components/Skeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

defineProps({ statistics: Array });

const getPerformanceColor = (rate) => {
    if (rate >= 80) return 'text-emerald-600';
    if (rate >= 60) return 'text-accent-600';
    if (rate >= 40) return 'text-orange-600';
    return 'text-red-600';
};

const initials = (name) => (name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
</script>

<template>
    <Head title="Performance des agents" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Performance des agents</h1>
                <p class="text-sm text-slate-500">Charge, résolution et délais par agent.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Sommaire -->
            <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total assignés</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ statistics.reduce((s, x) => s + x.assigned, 0) }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Résolus</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ statistics.reduce((s, x) => s + x.resolved, 0) }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">En attente</p>
                    <p class="mt-2 text-3xl font-bold text-accent-600">{{ statistics.reduce((s, x) => s + x.pending, 0) }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Taux moyen</p>
                    <p class="mt-2 text-3xl font-bold text-primary">
                        {{ statistics.length > 0 ? Math.round(statistics.reduce((s, x) => s + x.resolution_rate, 0) / statistics.length) : 0 }}%
                    </p>
                </div>
            </section>

            <section class="card overflow-hidden">
                <div v-if="!isLoading && !statistics.length" class="px-6 py-16 text-center text-sm text-slate-500">
                    Aucune donnée de performance disponible.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3">Agent</th>
                                <th class="px-6 py-3">Rôle</th>
                                <th class="px-6 py-3">Assignés</th>
                                <th class="px-6 py-3">Résolus</th>
                                <th class="px-6 py-3">En attente</th>
                                <th class="px-6 py-3">Taux</th>
                                <th class="px-6 py-3">Temps moyen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <TableSkeleton v-if="isLoading" :rows="6" :cols="7" />
                            <tr v-for="stat in (isLoading ? [] : statistics)" :key="stat.agent.id" class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                                            {{ initials(stat.agent.name) }}
                                        </span>
                                        <span class="font-medium text-slate-900">{{ stat.agent.name }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        v-for="role in stat.agent.roles"
                                        :key="role.id"
                                        class="badge bg-primary-100 text-primary-700"
                                    >
                                        {{ role.name }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-900">{{ stat.assigned }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-emerald-600">{{ stat.resolved }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-accent-600">{{ stat.pending }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['font-bold', getPerformanceColor(stat.resolution_rate)]">
                                        {{ stat.resolution_rate }}%
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-700">{{ stat.avg_resolution_hours }}h</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
