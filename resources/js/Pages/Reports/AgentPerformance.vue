<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    statistics: Array,
});

const getPerformanceColor = (rate) => {
    if (rate >= 80) return 'text-green-600';
    if (rate >= 60) return 'text-yellow-600';
    if (rate >= 40) return 'text-orange-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Performance des agents" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Performance des agents
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="statistics.length === 0" class="text-center text-gray-500">
                            Aucune donnée de performance disponible
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Agent
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Rôle
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Assignés
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Résolus
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            En attente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Taux résolution
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Temps moyen (h)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="stat in statistics"
                                        :key="stat.agent.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ stat.agent.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            <span
                                                v-for="role in stat.agent.roles"
                                                :key="role.id"
                                                class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ stat.assigned }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-green-600">
                                            {{ stat.resolved }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-orange-600">
                                            {{ stat.pending }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                :class="getPerformanceColor(stat.resolution_rate)"
                                                class="font-semibold"
                                            >
                                                {{ stat.resolution_rate }}%
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ stat.avg_resolution_hours }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Total tickets assignés
                            </div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ statistics.reduce((sum, s) => sum + s.assigned, 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Total résolus
                            </div>
                            <div class="mt-2 text-3xl font-bold text-green-600">
                                {{ statistics.reduce((sum, s) => sum + s.resolved, 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Total en attente
                            </div>
                            <div class="mt-2 text-3xl font-bold text-orange-600">
                                {{ statistics.reduce((sum, s) => sum + s.pending, 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Taux résolution moyen
                            </div>
                            <div class="mt-2 text-3xl font-bold text-blue-600">
                                {{ statistics.length > 0 
                                    ? Math.round(statistics.reduce((sum, s) => sum + s.resolution_rate, 0) / statistics.length) 
                                    : 0 }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>