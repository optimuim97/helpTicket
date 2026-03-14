<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    statistics: Object,
    dateRange: String,
});

const selectedRange = ref(props.dateRange);

const changeRange = () => {
    router.get(route('reports.global-statistics'), { range: selectedRange.value });
};
</script>

<template>
    <Head title="Statistiques globales" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Statistiques globales
                </h2>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Période:</label>
                    <select
                        v-model="selectedRange"
                        @change="changeRange"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="week">7 derniers jours</option>
                        <option value="month">30 derniers jours</option>
                        <option value="year">12 derniers mois</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Main Stats Card -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Total tickets
                            </div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ statistics.total_tickets }}
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Temps moyen de traitement
                            </div>
                            <div class="mt-2 text-3xl font-bold text-blue-600">
                                {{ statistics.avg_handling_hours }}h
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Période d'analyse
                            </div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ dateRange === 'week' ? '7 jours' : dateRange === 'year' ? '1 an' : '30 jours' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- By Status -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Par statut</h3>
                        </div>
                        <div class="p-6">
                            <div v-if="statistics.by_status.length === 0" class="text-center text-gray-500">
                                Aucune donnée
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="item in statistics.by_status"
                                    :key="item.status_id"
                                    class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ item.status.name }}
                                    </div>
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ item.count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- By Priority -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Par priorité</h3>
                        </div>
                        <div class="p-6">
                            <div v-if="statistics.by_priority.length === 0" class="text-center text-gray-500">
                                Aucune donnée
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="item in statistics.by_priority"
                                    :key="item.priority_id"
                                    class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                                >
                                    <div class="flex items-center space-x-2">
                                        <span
                                            :style="{ backgroundColor: item.priority.color }"
                                            class="inline-block h-3 w-3 rounded-full"
                                        ></span>
                                        <span class="font-medium text-gray-900">
                                            {{ item.priority.name }}
                                        </span>
                                    </div>
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ item.count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- By Type -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Par type</h3>
                        </div>
                        <div class="p-6">
                            <div v-if="statistics.by_type.length === 0" class="text-center text-gray-500">
                                Aucune donnée
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="item in statistics.by_type"
                                    :key="item.type_id"
                                    class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ item.type.name }}
                                    </div>
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ item.count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- By Channel -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Par canal</h3>
                        </div>
                        <div class="p-6">
                            <div v-if="statistics.by_channel.length === 0" class="text-center text-gray-500">
                                Aucune donnée
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="item in statistics.by_channel"
                                    :key="item.channel_id"
                                    class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ item.channel.name }}
                                    </div>
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ item.count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>