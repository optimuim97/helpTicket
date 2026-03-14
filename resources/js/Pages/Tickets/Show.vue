<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const activeTab = ref('details');

// Extend deadline form
const deadlineForm = useForm({
    due_date: '',
});

const submitDeadlineExtension = () => {
    deadlineForm.post(route('tickets.extend-deadline', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            deadlineForm.reset();
        },
    });
};

// Note form
const noteForm = useForm({
    note: '',
    is_internal: false,
});

const submitNote = () => {
    noteForm.post(route('tickets.notes.store', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            noteForm.reset();
        },
    });
};

// Attachment form
const attachmentForm = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    attachmentForm.file = event.target.files[0];
};

const submitAttachment = () => {
    attachmentForm.post(route('tickets.attachments.store', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            attachmentForm.reset();
            document.getElementById('file-upload').value = '';
        },
    });
};

// Assignment form
const assignForm = useForm({
    assigned_to: props.ticket.assigned_to?.id || '',
});

const submitAssignment = () => {
    assignForm.post(route('tickets.assign', props.ticket.id), {
        preserveScroll: true,
    });
};

// Close ticket
const closeTicket = () => {
    if (confirm('Êtes-vous sûr de vouloir fermer ce ticket ?')) {
        router.post(route('tickets.close', props.ticket.id));
    }
};

// Resolve ticket
const resolveTicket = () => {
    if (confirm('Êtes-vous sûr de vouloir marquer ce ticket comme résolu ?')) {
        router.post(route('tickets.resolve', props.ticket.id));
    }
};

// Delete ticket
const deleteTicket = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce ticket ? Cette action est irréversible.')) {
        router.delete(route('tickets.destroy', props.ticket.id));
    }
};

// Delete attachment
const deleteAttachment = (attachmentId) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')) {
        router.delete(route('tickets.attachments.destroy', [props.ticket.id, attachmentId]), {
            preserveScroll: true,
        });
    }
};

const getPriorityColor = (priority) => {
    const colors = {
        'Urgent': 'bg-red-100 text-red-800',
        'Haut': 'bg-orange-100 text-orange-800',
        'Moyen': 'bg-blue-100 text-blue-800',
        'Bas': 'bg-gray-100 text-gray-800',
    };
    return colors[priority] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status) => {
    const colors = {
        'Nouveau': 'bg-blue-100 text-blue-800',
        'En cours': 'bg-yellow-100 text-yellow-800',
        'En attente': 'bg-purple-100 text-purple-800',
        'Résolu': 'bg-green-100 text-green-800',
        'Fermé': 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getDeadlineStatus = () => {
    if (!props.ticket.due_date) return null;
    
    const now = new Date();
    const dueDate = new Date(props.ticket.due_date);
    const diffHours = (dueDate - now) / (1000 * 60 * 60);
    
    if (diffHours < 0) return 'overdue';
    if (diffHours < 24) return 'urgent';
    return 'normal';
};

const formatDeadline = (date) => {
    if (!date) return 'Non défini';
    return new Date(date).toLocaleString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Ticket {{ ticket.ticket_number }}
                    </h2>
                    <p class="text-sm text-gray-600">{{ ticket.subject }}</p>
                </div>
                <div class="flex space-x-2">
                    <SecondaryButton v-if="canUpdate" @click="$inertia.visit(route('tickets.edit', ticket.id))">
                        Modifier
                    </SecondaryButton>
                    <PrimaryButton 
                        v-if="canResolve && ticket.status?.name !== 'Résolu' && ticket.status?.name !== 'Fermé'" 
                        @click="resolveTicket"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        Marquer comme résolu
                    </PrimaryButton>
                    <PrimaryButton v-if="canClose && !ticket.status.is_closed" @click="closeTicket">
                        Fermer
                    </PrimaryButton>
                    <DangerButton v-if="canDelete" @click="deleteTicket">
                        Supprimer
                    </DangerButton>
                </div>
                    <!-- <DangerButton v-if="canDelete" @click="deleteTicket">
                        Supprimer
                    </DangerButton> -->
                <!-- </div> -->
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <!-- Tabs -->
                        <div class="mb-4 border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    @click="activeTab = 'details'"
                                    :class="[
                                        'border-b-2 px-1 py-4 text-sm font-medium',
                                        activeTab === 'details'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    ]"
                                >
                                    Détails
                                </button>
                                <button
                                    @click="activeTab = 'notes'"
                                    :class="[
                                        'border-b-2 px-1 py-4 text-sm font-medium',
                                        activeTab === 'notes'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    ]"
                                >
                                    Notes ({{ ticket.ticket_notes?.length || 0 }})
                                </button>
                                <button
                                    @click="activeTab = 'attachments'"
                                    :class="[
                                        'border-b-2 px-1 py-4 text-sm font-medium',
                                        activeTab === 'attachments'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    ]"
                                >
                                    Pièces jointes ({{ ticket.attachments?.length || 0 }})
                                </button>
                                <button
                                    @click="activeTab = 'history'"
                                    :class="[
                                        'border-b-2 px-1 py-4 text-sm font-medium',
                                        activeTab === 'history'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    ]"
                                >
                                    Historique
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <!-- Details Tab -->
                            <div v-if="activeTab === 'details'" class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                                <div class="mt-2 whitespace-pre-wrap text-gray-700">{{ ticket.description }}</div>

                                <div class="mt-6 grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Type</div>
                                        <div class="mt-1 text-sm text-gray-900">{{ ticket.type?.name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Canal</div>
                                        <div class="mt-1 text-sm text-gray-900">{{ ticket.channel?.name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Créé par</div>
                                        <div class="mt-1 text-sm text-gray-900">{{ ticket.created_by?.name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Créé le</div>
                                        <div class="mt-1 text-sm text-gray-900">
                                            {{ new Date(ticket.created_at).toLocaleString('fr-FR') }}
                                        </div>
                                    </div>
                                    <div v-if="ticket.resolved_at">
                                        <div class="text-sm font-medium text-gray-500">Résolu le</div>
                                        <div class="mt-1 text-sm text-gray-900">
                                            {{ new Date(ticket.resolved_at).toLocaleString('fr-FR') }}
                                        </div>
                                    </div>
                                    <div v-if="ticket.closed_at">
                                        <div class="text-sm font-medium text-gray-500">Fermé le</div>
                                        <div class="mt-1 text-sm text-gray-900">
                                            {{ new Date(ticket.closed_at).toLocaleString('fr-FR') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Tab -->
                            <div v-if="activeTab === 'notes'" class="p-6">
                                <!-- Add Note Form -->
                                <form v-if="canUpdate" @submit.prevent="submitNote" class="mb-6">
                                    <InputLabel for="note" value="Ajouter une note" />
                                    <textarea
                                        id="note"
                                        v-model="noteForm.note"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows="3"
                                        placeholder="Écrire une note..."
                                        required
                                    ></textarea>
                                    <InputError class="mt-2" :message="noteForm.errors.note" />

                                    <div class="mt-2 flex items-center justify-between">
                                        <label class="flex items-center">
                                            <input
                                                v-model="noteForm.is_internal"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            />
                                            <span class="ml-2 text-sm text-gray-600">Note interne</span>
                                        </label>
                                        <PrimaryButton :disabled="noteForm.processing">Ajouter</PrimaryButton>
                                    </div>
                                </form>

                                <!-- Notes List -->
                                <div v-if="ticket.ticket_notes && ticket.ticket_notes.length > 0" class="space-y-4">
                                    <div
                                        v-for="note in ticket.ticket_notes"
                                        :key="note.id"
                                        class="rounded-lg border border-gray-200 p-4"
                                        :class="{ 'bg-yellow-50': note.is_internal }"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ note.user?.name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ new Date(note.created_at).toLocaleString('fr-FR') }}
                                                </div>
                                            </div>
                                            <span v-if="note.is_internal" class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">
                                                Interne
                                            </span>
                                        </div>
                                        <div class="mt-2 whitespace-pre-wrap text-sm text-gray-700">{{ note.note }}</div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500">
                                    Aucune note pour ce ticket
                                </div>
                            </div>

                            <!-- Attachments Tab -->
                            <div v-if="activeTab === 'attachments'" class="p-6">
                                <!-- Upload Form -->
                                <form v-if="canUpdate" @submit.prevent="submitAttachment" class="mb-6">
                                    <InputLabel for="file-upload" value="Ajouter une pièce jointe" />
                                    <input
                                        id="file-upload"
                                        type="file"
                                        @change="handleFileUpload"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                                        required
                                    />
                                    <InputError class="mt-2" :message="attachmentForm.errors.file" />
                                    <div class="mt-2">
                                        <PrimaryButton :disabled="attachmentForm.processing || !attachmentForm.file">
                                            Télécharger
                                        </PrimaryButton>
                                    </div>
                                </form>

                                <!-- Attachments List -->
                                <div v-if="ticket.attachments && ticket.attachments.length > 0" class="space-y-3">
                                    <div
                                        v-for="attachment in ticket.attachments"
                                        :key="attachment.id"
                                        class="flex items-center justify-between rounded-lg border border-gray-200 p-4"
                                    >
                                        <div>
                                            <div class="font-medium text-gray-900">{{ attachment.original_name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ (attachment.size / 1024).toFixed(2) }} KB • 
                                                Téléchargé le {{ new Date(attachment.created_at).toLocaleString('fr-FR') }}
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a
                                                :href="route('tickets.attachments.download', [ticket.id, attachment.id])"
                                                class="text-blue-600 hover:text-blue-800"
                                            >
                                                Télécharger
                                            </a>
                                            <button
                                                v-if="canUpdate"
                                                @click="deleteAttachment(attachment.id)"
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500">
                                    Aucune pièce jointe pour ce ticket
                                </div>
                            </div>

                            <!-- History Tab -->
                            <div v-if="activeTab === 'history'" class="p-6">
                                <div v-if="ticket.history && ticket.history.length > 0" class="space-y-4">
                                    <div
                                        v-for="entry in ticket.history"
                                        :key="entry.id"
                                        class="border-l-4 border-blue-500 pl-4"
                                    >
                                        <div class="text-sm font-medium text-gray-900">{{ entry.action }}</div>
                                        <div class="text-xs text-gray-500">
                                            Par {{ entry.user?.name }} • {{ new Date(entry.created_at).toLocaleString('fr-FR') }}
                                        </div>
                                        <div v-if="entry.description" class="mt-1 text-sm text-gray-700">
                                            {{ entry.description }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500">
                                    Aucun historique disponible
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Status Card -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-sm font-medium text-gray-500">Statut</h3>
                                <span
                                    :class="getStatusColor(ticket.status?.name)"
                                    class="mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                >
                                    {{ ticket.status?.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Priority Card -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-sm font-medium text-gray-500">Priorité</h3>
                                <span
                                    :class="getPriorityColor(ticket.priority?.name)"
                                    class="mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                >
                                    {{ ticket.priority?.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Deadline Card -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-sm font-medium text-gray-500">Délai</h3>
                                <div 
                                    v-if="ticket.due_date"
                                    class="mt-2"
                                    :class="{
                                        'text-red-600 font-semibold': getDeadlineStatus() === 'overdue',
                                        'text-orange-600 font-semibold': getDeadlineStatus() === 'urgent',
                                        'text-gray-900': getDeadlineStatus() === 'normal'
                                    }"
                                >
                                    <div class="text-sm">{{ formatDeadline(ticket.due_date) }}</div>
                                    <div v-if="getDeadlineStatus() === 'overdue'" class="mt-1 text-xs">
                                        ⚠️ Délai dépassé
                                    </div>
                                    <div v-else-if="getDeadlineStatus() === 'urgent'" class="mt-1 text-xs">
                                        ⏰ Moins de 24h
                                    </div>
                                </div>
                                <div v-else class="mt-2 text-sm text-gray-500">
                                    Non défini
                                </div>

                                <!-- Extend Deadline Form -->
                                <form v-if="canExtendDeadline" @submit.prevent="submitDeadlineExtension" class="mt-4">
                                    <InputLabel for="due_date" value="Repousser le délai" />
                                    <input
                                        id="due_date"
                                        v-model="deadlineForm.due_date"
                                        type="datetime-local"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    <InputError class="mt-2" :message="deadlineForm.errors.due_date" />
                                    <PrimaryButton :disabled="deadlineForm.processing" class="mt-2 w-full">
                                        Repousser
                                    </PrimaryButton>
                                </form>
                            </div>
                        </div>

                        <!-- Assignment Card -->
                        <div v-if="canAssign && users" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="mb-2 text-sm font-medium text-gray-500">Assigner à</h3>
                                <form @submit.prevent="submitAssignment">
                                    <select
                                        v-model="assignForm.assigned_to"
                                        class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Non assigné</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <PrimaryButton :disabled="assignForm.processing" class="w-full">
                                        Assigner
                                    </PrimaryButton>
                                </form>
                            </div>
                        </div>

                        <!-- Assigned To (Read-only for non-supervisors) -->
                        <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-sm font-medium text-gray-500">Assigné à</h3>
                                <div class="mt-2 text-sm text-gray-900">
                                    {{ ticket.assigned_to?.name || 'Non assigné' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>