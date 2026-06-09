<script setup>
defineProps({
    name: { type: String, default: 'dot' },
});
</script>

<template>
    <svg
        class="h-5 w-5 shrink-0"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        viewBox="0 0 24 24"
        aria-hidden="true"
    >
        <!-- Tableau de bord -->
        <template v-if="name === 'home'">
            <path d="M3 12l9-9 9 9" />
            <path d="M5 10v10a1 1 0 001 1h3v-7h6v7h3a1 1 0 001-1V10" />
        </template>

        <!-- Tickets -->
        <template v-else-if="name === 'ticket'">
            <path d="M3 8a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 100 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 100-4V8z" />
            <path d="M13 6v12" stroke-dasharray="2 2" />
        </template>

        <!-- Projets -->
        <template v-else-if="name === 'folder'">
            <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
        </template>

        <!-- Rapports -->
        <template v-else-if="name === 'chart-bar'">
            <path d="M4 19V5" />
            <path d="M4 19h16" />
            <rect x="7" y="11" width="3" height="6" rx="0.5" />
            <rect x="12" y="7" width="3" height="10" rx="0.5" />
            <rect x="17" y="13" width="3" height="4" rx="0.5" />
        </template>

        <!-- Fiches PAA -->
        <template v-else-if="name === 'document'">
            <path d="M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
            <path d="M14 3v5h5" />
            <path d="M9 13h6M9 17h4" />
        </template>

        <!-- Administration / Settings -->
        <template v-else-if="name === 'cog'">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" />
        </template>

        <!-- Utilisateurs -->
        <template v-else-if="name === 'users'">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 00-3-3.87" />
            <path d="M16 3.13a4 4 0 010 7.75" />
        </template>

        <!-- Rôles / shield -->
        <template v-else-if="name === 'shield'">
            <path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z" />
            <path d="M9 12l2 2 4-4" />
        </template>

        <!-- Services / building -->
        <template v-else-if="name === 'building'">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01" />
        </template>

        <!-- Permissions / key -->
        <template v-else-if="name === 'key'">
            <circle cx="8" cy="15" r="4" />
            <path d="M10.85 12.15L20 3" />
            <path d="M17 6l3 3" />
            <path d="M15 8l3 3" />
        </template>

        <!-- Paramètres / sliders -->
        <template v-else-if="name === 'sliders'">
            <line x1="4" y1="6" x2="20" y2="6" />
            <line x1="4" y1="12" x2="20" y2="12" />
            <line x1="4" y1="18" x2="20" y2="18" />
            <circle cx="8" cy="6" r="2" fill="currentColor" />
            <circle cx="16" cy="12" r="2" fill="currentColor" />
            <circle cx="10" cy="18" r="2" fill="currentColor" />
        </template>

        <!-- Équipement / monitor -->
        <template v-else-if="name === 'monitor'">
            <rect x="2" y="4" width="20" height="13" rx="2" />
            <line x1="8" y1="21" x2="16" y2="21" />
            <line x1="12" y1="17" x2="12" y2="21" />
        </template>

        <!-- Intervention / wrench -->
        <template v-else-if="name === 'wrench'">
            <path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.7 2.7-2.6-2.6 2.7-2.7z" />
        </template>

        <!-- Profile / user -->
        <template v-else-if="name === 'user'">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </template>

        <!-- Logout -->
        <template v-else-if="name === 'logout'">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
            <path d="M16 17l5-5-5-5" />
            <path d="M21 12H9" />
        </template>

        <!-- Fallback : point -->
        <template v-else>
            <circle cx="12" cy="12" r="3" fill="currentColor" />
        </template>
    </svg>
</template>
