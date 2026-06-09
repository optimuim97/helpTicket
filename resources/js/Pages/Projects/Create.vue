<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    statuses: Object,
    types: Array,
    priorities: Array,
    channels: Array,
    users: Array,
});

const form = useForm({
    name: '',
    description: '',
    status: 'active',
    start_date: '',
    due_date: '',
    tasks: [],
});

const addTask = () => {
    form.tasks.push({
        subject: '',
        description: '',
        type_id: props.types?.[0]?.id ?? '',
        priority_id: props.priorities?.find(p => p.name === 'Moyen')?.id ?? props.priorities?.[0]?.id ?? '',
        assigned_to: '',
        due_date: '',
    });
};

const removeTask = (idx) => form.tasks.splice(idx, 1);

const submit = () => form.post(route('projects.store'));

const inputClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head title="Nouveau projet" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Nouveau projet</h1>
                <p class="text-sm text-slate-500">Créez un projet et ajoutez directement vos premières tâches.</p>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- INFOS PROJET -->
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Informations</h2>
                    <div class="mt-5 space-y-5">
                        <div>
                            <InputLabel for="name" value="Nom du projet *" />
                            <TextInput id="name" v-model="form.name" type="text" required autofocus class="mt-1.5" placeholder="Ex : Refonte portail client" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea id="description" v-model="form.description" rows="3" :class="inputClass" placeholder="Objectif, périmètre, parties prenantes…"></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                            <div>
                                <InputLabel for="status" value="Statut *" />
                                <select id="status" v-model="form.status" required :class="inputClass">
                                    <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                            <div>
                                <InputLabel for="start_date" value="Début" />
                                <input id="start_date" v-model="form.start_date" type="date" :class="inputClass" />
                                <InputError class="mt-2" :message="form.errors.start_date" />
                            </div>
                            <div>
                                <InputLabel for="due_date" value="Fin prévue" />
                                <input id="due_date" v-model="form.due_date" type="date" :class="inputClass" />
                                <InputError class="mt-2" :message="form.errors.due_date" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- TÂCHES INITIALES -->
                <section class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h2 class="text-base font-semibold text-slate-800">Tâches initiales</h2>
                            <p class="text-xs text-slate-500">
                                {{ form.tasks.length === 0 ? 'Aucune pour l\'instant — ajoutez-en pour démarrer.' : `${form.tasks.length} tâche${form.tasks.length > 1 ? 's' : ''} sera${form.tasks.length > 1 ? 'ont' : ''} créée${form.tasks.length > 1 ? 's' : ''} avec le projet.` }}
                            </p>
                        </div>
                        <button type="button" @click="addTask" class="btn-accent">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Ajouter une tâche
                        </button>
                    </div>

                    <div v-if="form.tasks.length === 0" class="px-6 py-10 text-center text-sm text-slate-500">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        Vous pourrez toujours ajouter des tâches plus tard depuis la page du projet.
                    </div>

                    <ul v-else class="divide-y divide-slate-100">
                        <li
                            v-for="(task, idx) in form.tasks"
                            :key="idx"
                            class="p-6"
                        >
                            <div class="flex items-start gap-3">
                                <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700">
                                    {{ idx + 1 }}
                                </span>
                                <div class="flex-1 space-y-3">
                                    <input
                                        v-model="task.subject"
                                        type="text"
                                        :placeholder="`Sujet de la tâche #${idx + 1}`"
                                        required
                                        class="block w-full border-0 border-b border-slate-200 bg-transparent px-0 py-1.5 text-base font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-0"
                                    />
                                    <InputError :message="form.errors[`tasks.${idx}.subject`]" />

                                    <textarea
                                        v-model="task.description"
                                        rows="2"
                                        placeholder="Description (facultative)…"
                                        class="block w-full resize-y rounded-lg border-slate-200 bg-slate-50 text-sm text-slate-700 shadow-sm focus:border-primary focus:ring-primary"
                                    ></textarea>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                                        <div>
                                            <label class="text-xs font-medium text-slate-500">Type *</label>
                                            <select v-model="task.type_id" required
                                                class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                                <option value="">…</option>
                                                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                                            </select>
                                            <InputError :message="form.errors[`tasks.${idx}.type_id`]" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-500">Priorité *</label>
                                            <select v-model="task.priority_id" required
                                                class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                                <option v-for="p in priorities" :key="p.id" :value="p.id">{{ p.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-500">Assigné à</label>
                                            <select v-model="task.assigned_to"
                                                class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                                <option value="">—</option>
                                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-500">Échéance</label>
                                            <input v-model="task.due_date" type="datetime-local"
                                                class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary" />
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeTask(idx)"
                                    class="rounded-md p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                                    title="Retirer cette tâche"
                                    aria-label="Retirer"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    </ul>

                    <div v-if="form.tasks.length > 0" class="border-t border-slate-100 bg-slate-50 px-6 py-3">
                        <button type="button" @click="addTask" class="text-sm font-medium text-primary hover:text-primary-700">
                            + Ajouter une autre tâche
                        </button>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 pb-12">
                    <SecondaryButton type="button" @click="$inertia.visit(route('projects.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Création…' : (form.tasks.length > 0 ? `Créer projet + ${form.tasks.length} tâche${form.tasks.length > 1 ? 's' : ''}` : 'Créer le projet') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
