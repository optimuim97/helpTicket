<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Skeleton from '@/Components/Skeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

const props = defineProps({
    statistics: Object,
    dateRange: String,
});

const selectedRange = ref(props.dateRange);
const changeRange = () => router.get(route('reports.global-statistics'), { range: selectedRange.value });

const rangeLabels = { week: '7 jours', month: '30 jours', year: '1 an' };
</script>

<template>
    <Head title="Statistiques globales" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Statistiques globales</h1>
                    <p class="text-sm text-slate-500">Vision agrégée de l'activité support.</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600">Période</label>
                    <select
                        v-model="selectedRange"
                        @change="changeRange"
                        class="rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary"
                    >
                        <option value="week">7 derniers jours</option>
                        <option value="month">30 derniers jours</option>
                        <option value="year">12 derniers mois</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- KPIs -->
            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total tickets</p>
                    <Skeleton v-if="isLoading" width="w-20" height="h-8" class="mt-2" />
                    <p v-else class="mt-2 text-3xl font-bold text-slate-900">{{ statistics.total_tickets }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Temps moyen</p>
                    <Skeleton v-if="isLoading" width="w-20" height="h-8" class="mt-2" />
                    <p v-else class="mt-2 text-3xl font-bold text-primary">{{ statistics.avg_handling_hours }}h</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Période analysée</p>
                    <Skeleton v-if="isLoading" width="w-20" height="h-8" class="mt-2" />
                    <p v-else class="mt-2 text-3xl font-bold text-slate-900">{{ rangeLabels[dateRange] || dateRange }}</p>
                </div>
            </section>

            <!-- Breakdowns -->
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="card">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-800">Par statut</h3>
                    </header>
                    <div class="p-6">
                        <p v-if="!statistics.by_status.length" class="text-center text-sm text-slate-500">Aucune donnée.</p>
                        <div v-else class="space-y-2">
                            <div
                                v-for="item in statistics.by_status"
                                :key="item.status_id"
                                class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3"
                            >
                                <span class="text-sm font-medium text-slate-700">{{ item.status.name }}</span>
                                <span class="text-xl font-bold text-primary">{{ item.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-800">Par priorité</h3>
                    </header>
                    <div class="p-6">
                        <p v-if="!statistics.by_priority.length" class="text-center text-sm text-slate-500">Aucune donnée.</p>
                        <div v-else class="space-y-2">
                            <div
                                v-for="item in statistics.by_priority"
                                :key="item.priority_id"
                                class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3"
                            >
                                <div class="flex items-center gap-2">
                                    <span :style="{ backgroundColor: item.priority.color }" class="inline-block h-2.5 w-2.5 rounded-full"></span>
                                    <span class="text-sm font-medium text-slate-700">{{ item.priority.name }}</span>
                                </div>
                                <span class="text-xl font-bold text-primary">{{ item.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-800">Par type</h3>
                    </header>
                    <div class="p-6">
                        <p v-if="!statistics.by_type.length" class="text-center text-sm text-slate-500">Aucune donnée.</p>
                        <div v-else class="space-y-2">
                            <div
                                v-for="item in statistics.by_type"
                                :key="item.type_id"
                                class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3"
                            >
                                <span class="text-sm font-medium text-slate-700">{{ item.type.name }}</span>
                                <span class="text-xl font-bold text-primary">{{ item.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-800">Par canal</h3>
                    </header>
                    <div class="p-6">
                        <p v-if="!statistics.by_channel.length" class="text-center text-sm text-slate-500">Aucune donnée.</p>
                        <div v-else class="space-y-2">
                            <div
                                v-for="item in statistics.by_channel"
                                :key="item.channel_id"
                                class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3"
                            >
                                <span class="text-sm font-medium text-slate-700">{{ item.channel.name }}</span>
                                <span class="text-xl font-bold text-primary">{{ item.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
