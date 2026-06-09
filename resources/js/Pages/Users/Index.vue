<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import CopyableField from '@/Components/CopyableField.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedRole = ref(props.filters?.role || '');

watch([search, selectedRole], () => {
    router.get(route('users.index'), {
        search: search.value,
        role: selectedRole.value,
    }, { preserveState: true, replace: true });
}, { throttle: 300 });

const deleteUser = (user) => {
    if (confirm(`Supprimer ${user.name} ?`)) {
        router.delete(route('users.destroy', user.id));
    }
};

const initials = (name) => (name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
const selectClass = 'block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';

// Pagination intelligente : 1 2 3 … 10 11 12 … 50
const pageWindow = computed(() => {
    const current = props.users.current_page;
    const last = props.users.last_page;
    if (!last || last <= 1) return [];
    const window = 1; // pages de chaque côté du courant
    const edge = 1;  // pages collées aux extrémités
    const pages = new Set();
    for (let i = 1; i <= edge; i++) pages.add(i);
    for (let i = last - edge + 1; i <= last; i++) pages.add(i);
    for (let i = current - window; i <= current + window; i++) {
        if (i >= 1 && i <= last) pages.add(i);
    }
    const sorted = [...pages].sort((a, b) => a - b);
    const result = [];
    let prev = 0;
    for (const p of sorted) {
        if (p - prev > 1) result.push('…');
        result.push(p);
        prev = p;
    }
    return result;
});

const goToPage = (page) => {
    router.get(route('users.index'), {
        page,
        search: search.value || undefined,
        role: selectedRole.value || undefined,
    }, { preserveState: true, replace: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Utilisateurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Utilisateurs</h1>
                    <p class="text-sm text-slate-500">Annuaire et gestion des comptes.</p>
                </div>
                <Link :href="route('users.create')" class="btn-accent">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvel utilisateur
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filtres -->
            <section class="card p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Recherche</label>
                        <TextInput v-model="search" type="text" placeholder="Nom, e-mail, matricule…" class="w-full" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Rôle</label>
                        <select v-model="selectedRole" :class="selectClass">
                            <option value="">Tous</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-500">{{ users.total }} utilisateur{{ users.total > 1 ? 's' : '' }}</p>
            </section>

            <!-- Tableau -->
            <section class="card overflow-hidden">
                <div v-if="!isLoading && users.data.length === 0" class="px-6 py-16 text-center text-sm text-slate-500">
                    Aucun utilisateur trouvé.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3">Utilisateur</th>
                                <th class="px-6 py-3">Matricule</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Fonction</th>
                                <th class="px-6 py-3">Contacts</th>
                                <th class="px-6 py-3">Service</th>
                                <th class="px-6 py-3">Rôle(s)</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <TableSkeleton v-if="isLoading" :rows="8" :cols="8" />
                            <tr
                                v-for="user in (isLoading ? [] : users.data)"
                                :key="user.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                                            {{ initials(user.name) }}
                                        </span>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900">{{ user.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <CopyableField :value="user.matricule" mono />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <CopyableField :value="user.email" />
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <span v-if="user.position">{{ user.position.fonction }}</span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <CopyableField v-if="user.numero_fixe" :value="user.numero_fixe" icon="📞" mono />
                                        <CopyableField v-if="user.numero_flotte" :value="user.numero_flotte" icon="📱" mono />
                                        <span v-if="!user.numero_fixe && !user.numero_flotte" class="text-slate-400">—</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span v-if="user.service" class="text-slate-700">{{ user.service.name }}</span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            class="badge bg-primary-100 text-primary-700"
                                        >
                                            {{ role.name }}
                                        </span>
                                        <span v-if="!user.roles?.length" class="text-xs text-slate-400">—</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <Link
                                            :href="route('users.edit', user.id)"
                                            class="rounded-md p-1.5 text-slate-400 transition hover:bg-primary-50 hover:text-primary"
                                            :title="`Modifier ${user.name}`"
                                            aria-label="Modifier"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deleteUser(user)"
                                            class="rounded-md p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                                            :title="`Supprimer ${user.name}`"
                                            aria-label="Supprimer"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="users.last_page > 1" class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold">{{ users.from }}</span>–<span class="font-semibold">{{ users.to }}</span>
                        sur <span class="font-semibold">{{ users.total }}</span> utilisateur{{ users.total > 1 ? 's' : '' }}
                    </p>
                    <nav class="flex flex-wrap items-center gap-1">
                        <button
                            type="button"
                            @click="goToPage(users.current_page - 1)"
                            :disabled="users.current_page <= 1"
                            class="rounded-md border border-slate-200 px-2.5 py-1.5 text-sm text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            aria-label="Page précédente"
                        >
                            ‹
                        </button>
                        <template v-for="(p, idx) in pageWindow" :key="idx">
                            <span v-if="p === '…'" class="px-1.5 text-sm text-slate-400">…</span>
                            <button
                                v-else
                                type="button"
                                @click="goToPage(p)"
                                :class="[
                                    'min-w-[2.25rem] rounded-md px-3 py-1.5 text-sm transition',
                                    p === users.current_page
                                        ? 'bg-primary text-white shadow-soft'
                                        : 'border border-slate-200 text-slate-700 hover:bg-slate-50',
                                ]"
                            >
                                {{ p }}
                            </button>
                        </template>
                        <button
                            type="button"
                            @click="goToPage(users.current_page + 1)"
                            :disabled="users.current_page >= users.last_page"
                            class="rounded-md border border-slate-200 px-2.5 py-1.5 text-sm text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            aria-label="Page suivante"
                        >
                            ›
                        </button>
                    </nav>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
