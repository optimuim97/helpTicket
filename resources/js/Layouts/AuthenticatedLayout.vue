<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import PageLoader from '@/Components/PageLoader.vue';
import SidebarIcon from '@/Components/SidebarIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
const page = usePage();

const mainNav = computed(() => page.props.navigation?.main || []);
const userNav = computed(() => page.props.navigation?.user || []);

// Branding PAA — ignore l'ancien défaut HELPTICKET même si la BDD n'a pas
// encore été re-migrée.
const appName = computed(() => {
    const v = page.props.appSettings?.app_name;
    return (!v || /helpticket/i.test(v)) ? "Port Autonome d'Abidjan" : v;
});
const appLogo = computed(() => {
    const v = page.props.appSettings?.app_logo;
    return v && !/helpticket/i.test(v) ? v : null;
});
const user = computed(() => page.props.auth.user);

const isItemActive = (item) => {
    if (!item.active) return false;
    const current = route().current();
    const pattern = item.active;
    if (pattern.includes('|')) {
        return pattern.split('|').some(p => matchPattern(p, current));
    }
    return matchPattern(pattern, current);
};
const matchPattern = (pattern, current) => {
    if (pattern.includes('*')) {
        return new RegExp(`^${pattern.replace(/\*/g, '.*')}$`).test(current);
    }
    return pattern === current;
};
const isParentActive = (item) => isItemActive(item) || (item.children?.some(c => isItemActive(c)) ?? false);

const expandedGroups = ref({});
const toggleGroup = (idx) => { expandedGroups.value[idx] = !expandedGroups.value[idx]; };
const isExpanded = (idx, item) => expandedGroups.value[idx] ?? isParentActive(item);

const initials = computed(() => {
    const name = user.value?.name || '';
    return name.split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
});
</script>

<template>
    <div>
        <PageLoader />
        <div class="min-h-screen bg-slate-50">
            <!-- Sidebar (fixe : ne scrolle pas avec le contenu) -->
            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col bg-white shadow-card transition-transform lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-16 items-center gap-3 border-b border-slate-100 px-4">
                    <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 ring-1 ring-slate-200">
                            <img
                                v-if="appLogo"
                                :src="`/storage/${appLogo}`"
                                :alt="appName"
                                class="h-full w-full object-contain"
                            />
                            <ApplicationLogo v-else />
                        </span>
                        <span class="truncate text-sm font-bold leading-tight text-slate-800" :title="appName">{{ appName }}</span>
                    </Link>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
                    <template v-for="(item, idx) in mainNav" :key="idx">
                        <div v-if="item.type === 'separator'" class="my-3 border-t border-slate-100"></div>

                        <div v-else-if="item.type === 'dropdown' && item.children" class="space-y-1">
                            <button
                                type="button"
                                @click="toggleGroup(idx)"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 font-medium transition"
                                :class="isParentActive(item) ? 'bg-primary-50 text-primary-700' : 'text-slate-600 hover:bg-slate-50'"
                            >
                                <span class="flex items-center gap-3">
                                    <SidebarIcon :name="item.icon" />
                                    {{ item.label }}
                                </span>
                                <svg class="h-4 w-4 transition" :class="isExpanded(idx, item) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="isExpanded(idx, item)" class="ml-3 space-y-1 border-l border-slate-100 pl-2">
                                <template v-for="(child, cidx) in item.children" :key="cidx">
                                    <div v-if="child.type === 'separator'" class="my-2 border-t border-slate-100"></div>
                                    <Link
                                        v-else-if="child.route"
                                        :href="route(child.route)"
                                        :method="child.method || 'get'"
                                        :as="child.method === 'post' ? 'button' : 'a'"
                                        class="flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm transition"
                                        :class="isItemActive(child) ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    >
                                        <SidebarIcon :name="child.icon" class="h-4 w-4" />
                                        {{ child.label }}
                                    </Link>
                                </template>
                            </div>
                        </div>

                        <Link
                            v-else-if="item.route"
                            :href="route(item.route)"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition"
                            :class="isItemActive(item) ? 'bg-primary text-white shadow-soft' : 'text-slate-600 hover:bg-slate-50'"
                        >
                            <SidebarIcon :name="item.icon" />
                            {{ item.label }}
                        </Link>
                    </template>
                </nav>
            </aside>

            <!-- Backdrop mobile -->
            <div
                v-if="sidebarOpen"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
            ></div>

            <!-- Main column (avec marge à gauche pour la sidebar fixe en lg+) -->
            <div class="flex min-h-screen flex-col lg:pl-64">
                <!-- Topbar -->
                <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-100 bg-white/80 px-4 backdrop-blur sm:px-6">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="hidden flex-1 md:block">
                        <div class="relative max-w-md">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                            <input
                                type="search"
                                placeholder="Rechercher..."
                                class="w-full rounded-lg border-slate-200 bg-slate-50 pl-9 text-sm focus:border-primary focus:ring-primary"
                            />
                        </div>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Notifications">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>

                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-lg p-1.5 pr-3 text-left hover:bg-slate-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">
                                        {{ initials || '?' }}
                                    </span>
                                    <span class="hidden text-sm sm:block">
                                        <span class="block font-semibold text-slate-800">{{ user.name }}</span>
                                        <span class="block text-xs text-slate-500">{{ user.email }}</span>
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <template v-for="(item, idx) in userNav" :key="idx">
                                    <div v-if="item.type === 'separator'" class="my-1 border-t border-slate-100"></div>
                                    <DropdownLink
                                        v-else
                                        :href="item.route ? route(item.route) : '#'"
                                        :method="item.method || 'get'"
                                        :as="item.method === 'post' ? 'button' : 'a'"
                                    >
                                        {{ item.label }}
                                    </DropdownLink>
                                </template>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <header v-if="$slots.header" class="border-b border-slate-100 bg-white px-4 py-5 sm:px-8">
                    <slot name="header" />
                </header>

                <main class="flex-1 px-4 py-6 sm:px-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
