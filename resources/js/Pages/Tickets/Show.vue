<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    ticket: Object,
    users: Array,
    canUpdate: Boolean,
    canAssign: Boolean,
    canClose: Boolean,
    canDelete: Boolean,
    canResolve: Boolean,
    canExtendDeadline: Boolean,
});

// === Notes (commentaires)
const noteForm = useForm({ note: '', is_internal: false });
const submitNote = () => noteForm.post(route('tickets.notes.store', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => noteForm.reset(),
});

// === Pièces jointes
const attachmentForm = useForm({ file: null });
const fileName = ref('');
const handleFileUpload = (e) => {
    const f = e.target.files[0];
    attachmentForm.file = f;
    fileName.value = f?.name || '';
};
const submitAttachment = () => attachmentForm.post(route('tickets.attachments.store', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => {
        attachmentForm.reset();
        fileName.value = '';
        const el = document.getElementById('file-upload');
        if (el) el.value = '';
    },
});

// === Assignation
const assignForm = useForm({ assigned_to: props.ticket.assigned_to?.id || '' });
const submitAssignment = () => assignForm.post(route('tickets.assign', props.ticket.id), { preserveScroll: true });

// === Échéance
const deadlineForm = useForm({ due_date: '' });
const submitDeadlineExtension = () => deadlineForm.post(route('tickets.extend-deadline', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => deadlineForm.reset(),
});

// === Actions globales
const closeTicket = () => confirm('Fermer ce ticket ?') && router.post(route('tickets.close', props.ticket.id));
const resolveTicket = () => confirm('Marquer ce ticket comme résolu ?') && router.post(route('tickets.resolve', props.ticket.id));
const deleteTicket = () => confirm('Supprimer ce ticket ? Action irréversible.') && router.delete(route('tickets.destroy', props.ticket.id));
const deleteAttachment = (id) => confirm('Supprimer cette pièce jointe ?') && router.delete(route('tickets.attachments.destroy', [props.ticket.id, id]), { preserveScroll: true });

// === Badges
const priorityBadge = (p) => ({
    'Urgent': 'bg-red-100 text-red-700 ring-1 ring-red-200',
    'Haut': 'bg-accent-100 text-accent-700 ring-1 ring-accent-200',
    'Moyen': 'bg-primary-100 text-primary-700 ring-1 ring-primary-200',
    'Bas': 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
}[p] || 'bg-slate-100 text-slate-600 ring-1 ring-slate-200');

const statusBadge = (s) => ({
    'Nouveau': 'bg-primary-100 text-primary-700 ring-1 ring-primary-200',
    'En cours': 'bg-accent-100 text-accent-700 ring-1 ring-accent-200',
    'En attente': 'bg-purple-100 text-purple-700 ring-1 ring-purple-200',
    'Résolu': 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
    'Fermé': 'bg-slate-200 text-slate-700 ring-1 ring-slate-300',
}[s] || 'bg-slate-100 text-slate-600 ring-1 ring-slate-200');

// === Délais
const deadlineStatus = computed(() => {
    if (!props.ticket.due_date) return null;
    const diffHours = (new Date(props.ticket.due_date) - new Date()) / 3600000;
    if (diffHours < 0) return 'overdue';
    if (diffHours < 24) return 'urgent';
    return 'normal';
});
const formatDateTime = (d) => d ? new Date(d).toLocaleString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
const timeAgo = (d) => {
    if (!d) return '';
    const diff = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
    if (diff < 60) return 'à l\'instant';
    if (diff < 3600) return `il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `il y a ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `il y a ${Math.floor(diff / 86400)} j`;
    return formatDate(d);
};

const initials = (name) => (name || '?').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();

// === Activity stream : merge notes + history triés chrono
const activity = computed(() => {
    const items = [];
    (props.ticket.ticket_notes ?? []).forEach(n => items.push({ kind: 'note', date: n.created_at, data: n }));
    (props.ticket.history ?? []).forEach(h => items.push({ kind: 'event', date: h.created_at, data: h }));
    return items.sort((a, b) => new Date(a.date) - new Date(b.date));
});

const isClosed = computed(() => props.ticket.status?.is_closed);
const isResolved = computed(() => props.ticket.resolved_at !== null);
</script>

<template>
    <Head :title="`${ticket.ticket_number} — ${ticket.subject}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4">
                <Link :href="route('tickets.index')" class="inline-flex w-fit items-center gap-1.5 text-sm text-slate-500 transition hover:text-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour aux tickets
                </Link>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-semibold uppercase tracking-wider text-primary">
                                {{ ticket.ticket_number }}
                            </span>
                            <span class="text-slate-300">•</span>
                            <span :class="['badge px-2.5 py-1', statusBadge(ticket.status?.name)]">
                                <span class="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-current opacity-80"></span>
                                {{ ticket.status?.name }}
                            </span>
                            <span :class="['badge px-2.5 py-1', priorityBadge(ticket.priority?.name)]">
                                {{ ticket.priority?.name }}
                            </span>
                            <span v-if="ticket.type" class="badge bg-slate-100 px-2.5 py-1 text-slate-600 ring-1 ring-slate-200">
                                {{ ticket.type.name }}
                            </span>
                        </div>
                        <h1 class="mt-2 text-2xl font-bold leading-snug text-slate-900 sm:text-3xl">
                            {{ ticket.subject }}
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-500">
                            Ouvert par <span class="font-medium text-slate-700">{{ ticket.created_by?.name || 'N/A' }}</span>
                            <span class="text-slate-400"> · {{ timeAgo(ticket.created_at) }}</span>
                        </p>
                    </div>

                    <!-- Actions principales -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="canResolve && !isResolved && !isClosed"
                            @click="resolveTicket"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:bg-emerald-700"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Résoudre
                        </button>
                        <SecondaryButton v-if="canUpdate" @click="$inertia.visit(route('tickets.edit', ticket.id))">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Modifier
                        </SecondaryButton>
                        <PrimaryButton v-if="canClose && !isClosed" @click="closeTicket">Fermer</PrimaryButton>
                        <DangerButton v-if="canDelete" @click="deleteTicket">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                            </svg>
                        </DangerButton>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
            <!-- ╔═════════════ COLONNE PRINCIPALE ═════════════╗ -->
            <div class="space-y-6">
                <!-- DESCRIPTION -->
                <section class="card overflow-hidden">
                    <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                                {{ initials(ticket.created_by?.name) }}
                            </span>
                            <div class="text-sm">
                                <span class="font-medium text-slate-800">{{ ticket.created_by?.name || 'Inconnu' }}</span>
                                <span class="text-slate-500"> a ouvert ce ticket</span>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400" :title="formatDateTime(ticket.created_at)">{{ timeAgo(ticket.created_at) }}</span>
                    </header>
                    <div class="whitespace-pre-wrap p-5 text-sm leading-relaxed text-slate-700">{{ ticket.description }}</div>
                </section>

                <!-- FICHE PAA LIÉE -->
                <section v-if="ticket.linkable" class="card overflow-hidden border-l-4 border-l-primary">
                    <div class="flex items-start justify-between p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary">
                                <svg v-if="ticket.linkable_type === 'intervention_sheet'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.7 2.7-2.6-2.6 2.7-2.7z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <rect x="2" y="4" width="20" height="13" rx="2" />
                                    <line x1="8" y1="21" x2="16" y2="21" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-primary-700">
                                    {{ ticket.linkable_type === 'intervention_sheet' ? "Fiche d'intervention" : "Fiche d'affectation" }}
                                </p>
                                <p class="mt-0.5 font-semibold text-slate-900">{{ ticket.linkable.reference }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    <template v-if="ticket.linkable_type === 'intervention_sheet'">
                                        {{ ticket.linkable.site }} — {{ ticket.linkable.agent_name }}
                                    </template>
                                    <template v-else>
                                        {{ ticket.linkable.equipment_type }} — {{ ticket.linkable.agent_name }}
                                    </template>
                                </p>
                            </div>
                        </div>
                        <a
                            :href="ticket.linkable_type === 'intervention_sheet'
                                ? route('intervention-sheets.show', ticket.linkable.id)
                                : route('equipment-assignments.show', ticket.linkable.id)"
                            class="text-sm font-medium text-primary hover:text-primary-700"
                        >
                            Ouvrir →
                        </a>
                    </div>
                </section>

                <!-- ACTIVITÉ (notes + historique fusionnés chronologiquement) -->
                <section class="card overflow-hidden">
                    <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                            Activité
                            <span class="ml-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium normal-case text-slate-600">
                                {{ activity.length }}
                            </span>
                        </h2>
                    </header>

                    <div v-if="activity.length === 0" class="px-5 py-8 text-center text-sm text-slate-500">
                        Aucune activité pour l'instant. Ajoutez une note ci-dessous pour commencer.
                    </div>

                    <ul v-else class="relative space-y-0 px-5 py-4">
                        <li v-for="(item, idx) in activity" :key="`act-${idx}`" class="relative pl-9">
                            <!-- Trait vertical entre items -->
                            <span
                                v-if="idx < activity.length - 1"
                                class="absolute left-3.5 top-7 h-[calc(100%-1rem)] w-px bg-slate-200"
                            ></span>

                            <!-- NOTE -->
                            <div v-if="item.kind === 'note'" class="pb-5">
                                <span
                                    class="absolute left-0 top-0 flex h-7 w-7 items-center justify-center rounded-full text-[0.65rem] font-semibold ring-2 ring-white"
                                    :class="item.data.is_internal ? 'bg-accent text-white' : 'bg-primary text-white'"
                                >
                                    {{ initials(item.data.user?.name) }}
                                </span>
                                <div
                                    class="rounded-xl border p-4"
                                    :class="item.data.is_internal ? 'border-accent-200 bg-accent-50/50' : 'border-slate-200 bg-white'"
                                >
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <div class="text-sm">
                                            <span class="font-semibold text-slate-900">{{ item.data.user?.name || 'Inconnu' }}</span>
                                            <span class="text-slate-500"> a commenté</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span v-if="item.data.is_internal" class="badge bg-accent-100 text-accent-700">Interne</span>
                                            <span class="text-xs text-slate-400" :title="formatDateTime(item.data.created_at)">{{ timeAgo(item.data.created_at) }}</span>
                                        </div>
                                    </div>
                                    <div class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ item.data.note }}</div>
                                </div>
                            </div>

                            <!-- EVENT HISTORY -->
                            <div v-else class="pb-5">
                                <span class="absolute left-1 top-0 flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 ring-4 ring-white">
                                    <svg class="h-3 w-3 text-slate-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                </span>
                                <div class="flex items-baseline gap-2 text-sm">
                                    <span class="font-medium text-slate-700">{{ item.data.user?.name || 'Système' }}</span>
                                    <span class="text-slate-600">{{ item.data.action }}</span>
                                    <span class="text-xs text-slate-400" :title="formatDateTime(item.data.date)">· {{ timeAgo(item.data.date) }}</span>
                                </div>
                                <p v-if="item.data.description" class="ml-1 mt-1 text-sm text-slate-500">{{ item.data.description }}</p>
                            </div>
                        </li>
                    </ul>

                    <!-- COMPOSER de note inline -->
                    <form v-if="canUpdate" @submit.prevent="submitNote" class="border-t border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                                {{ initials($page.props.auth.user.name) }}
                            </span>
                            <div class="flex-1">
                                <textarea
                                    v-model="noteForm.note"
                                    rows="2"
                                    placeholder="Ajouter un commentaire… (Ctrl+Entrée pour envoyer)"
                                    @keydown.ctrl.enter="submitNote"
                                    required
                                    class="block w-full resize-y rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary"
                                ></textarea>
                                <InputError class="mt-1" :message="noteForm.errors.note" />
                                <div class="mt-2 flex items-center justify-between">
                                    <label class="flex items-center gap-2 text-xs text-slate-600">
                                        <input v-model="noteForm.is_internal" type="checkbox" class="h-3.5 w-3.5 rounded border-slate-300 text-accent focus:ring-accent" />
                                        Note interne (visible par l'équipe support uniquement)
                                    </label>
                                    <button
                                        type="submit"
                                        :disabled="noteForm.processing || !noteForm.note.trim()"
                                        class="btn-primary px-3 py-1.5 text-xs disabled:opacity-50"
                                    >
                                        {{ noteForm.processing ? '…' : 'Commenter' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <!-- ╔═════════════ SIDEBAR (sticky) ═════════════╗ -->
            <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
                <!-- Propriétés -->
                <section class="card overflow-hidden">
                    <header class="border-b border-slate-100 px-5 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Propriétés</h3>
                    </header>
                    <dl class="divide-y divide-slate-100 text-sm">
                        <!-- Assignation (éditable) -->
                        <div class="flex items-center gap-3 px-5 py-3">
                            <dt class="w-24 shrink-0 text-xs font-medium text-slate-500">Assigné à</dt>
                            <dd class="min-w-0 flex-1">
                                <form v-if="canAssign && users" @submit.prevent="submitAssignment" class="flex items-center gap-2">
                                    <select
                                        v-model="assignForm.assigned_to"
                                        @change="submitAssignment"
                                        class="block w-full rounded-md border-slate-200 bg-white py-1 text-sm shadow-sm focus:border-primary focus:ring-primary"
                                    >
                                        <option value="">Non assigné</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                </form>
                                <div v-else-if="ticket.assigned_to" class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-[0.6rem] font-semibold text-white">
                                        {{ initials(ticket.assigned_to.name) }}
                                    </span>
                                    <span class="truncate text-slate-900">{{ ticket.assigned_to.name }}</span>
                                </div>
                                <span v-else class="text-slate-400">Non assigné</span>
                            </dd>
                        </div>

                        <!-- Canal -->
                        <div class="flex items-center gap-3 px-5 py-3">
                            <dt class="w-24 shrink-0 text-xs font-medium text-slate-500">Canal</dt>
                            <dd class="text-slate-700">{{ ticket.channel?.name || '—' }}</dd>
                        </div>

                        <!-- Échéance -->
                        <div class="flex items-start gap-3 px-5 py-3">
                            <dt class="w-24 shrink-0 pt-0.5 text-xs font-medium text-slate-500">Échéance</dt>
                            <dd class="min-w-0 flex-1">
                                <div v-if="ticket.due_date" class="text-sm">
                                    <span :class="{
                                        'font-semibold text-red-600': deadlineStatus === 'overdue',
                                        'font-semibold text-accent-700': deadlineStatus === 'urgent',
                                        'text-slate-800': deadlineStatus === 'normal',
                                    }">{{ formatDateTime(ticket.due_date) }}</span>
                                    <p v-if="deadlineStatus === 'overdue'" class="mt-0.5 text-xs text-red-600">⚠️ Délai dépassé</p>
                                    <p v-else-if="deadlineStatus === 'urgent'" class="mt-0.5 text-xs text-accent-700">⏰ Moins de 24h</p>
                                </div>
                                <span v-else class="text-slate-400">Non définie</span>

                                <details v-if="canExtendDeadline" class="group mt-2">
                                    <summary class="cursor-pointer text-xs font-medium text-primary hover:text-primary-700">
                                        Repousser ↗
                                    </summary>
                                    <form @submit.prevent="submitDeadlineExtension" class="mt-2 space-y-2">
                                        <input
                                            v-model="deadlineForm.due_date"
                                            type="datetime-local"
                                            required
                                            class="block w-full rounded-md border-slate-200 bg-white py-1 text-xs shadow-sm focus:border-primary focus:ring-primary"
                                        />
                                        <InputError :message="deadlineForm.errors.due_date" />
                                        <button type="submit" :disabled="deadlineForm.processing" class="btn-primary w-full justify-center py-1 text-xs">
                                            Valider
                                        </button>
                                    </form>
                                </details>
                            </dd>
                        </div>

                        <!-- Créé le -->
                        <div class="flex items-center gap-3 px-5 py-3">
                            <dt class="w-24 shrink-0 text-xs font-medium text-slate-500">Créé le</dt>
                            <dd class="text-slate-700" :title="formatDateTime(ticket.created_at)">{{ formatDate(ticket.created_at) }}</dd>
                        </div>

                        <div v-if="ticket.resolved_at" class="flex items-center gap-3 px-5 py-3">
                            <dt class="w-24 shrink-0 text-xs font-medium text-slate-500">Résolu le</dt>
                            <dd class="text-emerald-700" :title="formatDateTime(ticket.resolved_at)">{{ formatDate(ticket.resolved_at) }}</dd>
                        </div>

                        <div v-if="ticket.closed_at" class="flex items-center gap-3 px-5 py-3">
                            <dt class="w-24 shrink-0 text-xs font-medium text-slate-500">Fermé le</dt>
                            <dd class="text-slate-700" :title="formatDateTime(ticket.closed_at)">{{ formatDate(ticket.closed_at) }}</dd>
                        </div>
                    </dl>
                </section>

                <!-- Pièces jointes -->
                <section class="card overflow-hidden">
                    <header class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Pièces jointes
                            <span v-if="ticket.attachments?.length" class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium normal-case text-slate-600">
                                {{ ticket.attachments.length }}
                            </span>
                        </h3>
                    </header>

                    <div v-if="ticket.attachments?.length" class="divide-y divide-slate-100">
                        <div
                            v-for="att in ticket.attachments"
                            :key="att.id"
                            class="group flex items-center gap-3 px-5 py-2.5 transition hover:bg-slate-50"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <a :href="route('tickets.attachments.download', [ticket.id, att.id])" class="block truncate text-xs font-medium text-slate-800 hover:text-primary" :title="att.original_name">
                                    {{ att.original_name }}
                                </a>
                                <p class="text-[0.65rem] text-slate-400">{{ (att.size / 1024).toFixed(0) }} KB</p>
                            </div>
                            <button
                                v-if="canUpdate"
                                @click="deleteAttachment(att.id)"
                                class="rounded p-1 text-slate-300 opacity-0 transition group-hover:opacity-100 hover:bg-red-50 hover:text-red-600"
                                :title="`Supprimer ${att.original_name}`"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="px-5 py-4 text-xs text-slate-500">Aucune pièce jointe.</p>

                    <form v-if="canUpdate" @submit.prevent="submitAttachment" class="border-t border-slate-100 bg-slate-50 p-3">
                        <label for="file-upload" class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-3 py-3 text-xs font-medium text-slate-600 transition hover:border-primary hover:bg-primary-50 hover:text-primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            {{ fileName || 'Choisir un fichier…' }}
                        </label>
                        <input id="file-upload" type="file" @change="handleFileUpload" class="sr-only" />
                        <InputError class="mt-1" :message="attachmentForm.errors.file" />
                        <button
                            v-if="attachmentForm.file"
                            type="submit"
                            :disabled="attachmentForm.processing"
                            class="btn-primary mt-2 w-full justify-center py-1.5 text-xs"
                        >
                            {{ attachmentForm.processing ? 'Envoi…' : 'Téléverser' }}
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
