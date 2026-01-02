<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import type { Expense, Participant, Room } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Props {
    room: Room;
    participants: Participant[];
    currentParticipant: Participant;
    expense?: Expense; // If provided, we're in edit mode
}

const props = defineProps<Props>();

const emit = defineEmits<{
    success: [];
}>();

// Check if we're editing
const isEditing = computed(() => !!props.expense);

const form = useForm({
    description: props.expense?.description || '',
    amount: props.expense ? parseFloat(props.expense.amount) : (null as number | null),
    payer_id: props.expense?.payer_id || props.currentParticipant.id,
    splits: [] as { participant_id: string; amount: number }[],
});

// Split mode: 'equal' or 'custom'
const splitMode = ref<'equal' | 'custom'>('equal');

// Selected participants for splitting
const getInitialSelection = () => {
    if (props.expense?.splits?.length) {
        return new Set(props.expense.splits.map((s) => s.participant_id));
    }
    return new Set(props.participants.map((p) => p.id));
};

const selectedParticipants = ref<Set<string>>(getInitialSelection());

// Toggle participant selection
const toggleParticipant = (id: string) => {
    if (selectedParticipants.value.has(id)) {
        if (selectedParticipants.value.size > 1) {
            selectedParticipants.value.delete(id);
        }
    } else {
        selectedParticipants.value.add(id);
    }
    selectedParticipants.value = new Set(selectedParticipants.value);
};

// Calculate equal splits
const equalSplitAmount = computed(() => {
    if (!form.amount || selectedParticipants.value.size === 0) return 0;
    return Math.round((form.amount / selectedParticipants.value.size) * 100) / 100;
});

// Update splits when amount or selection changes
watch(
    [() => form.amount, selectedParticipants, splitMode],
    () => {
        if (splitMode.value === 'equal') {
            form.splits = Array.from(selectedParticipants.value).map((id) => ({
                participant_id: id,
                amount: equalSplitAmount.value,
            }));
        }
    },
    { immediate: true },
);

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};

const submit = () => {
    const url = isEditing.value ? `/${props.room.code}/expenses/${props.expense!.id}` : `/${props.room.code}/expenses`;

    const method = isEditing.value ? 'put' : 'post';

    form[method](url, {
        onSuccess: () => {
            if (!isEditing.value) {
                form.reset();
                selectedParticipants.value = new Set(props.participants.map((p) => p.id));
            }
            emit('success');
        },
    });
};

// Button text
const buttonText = computed(() => (isEditing.value ? 'Guardar Cambios' : 'Agregar Gasto'));
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <!-- Description -->
        <Input v-model="form.description" label="¿En qué se gastó?" :error="form.errors.description" placeholder="Ej: Pizza, nafta, entrada..." />

        <!-- Amount -->
        <Input v-model="form.amount" type="number" label="Monto total" :error="form.errors.amount" inputmode="decimal" step="0.01" min="0.01" />

        <!-- Who paid -->
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-400"> ¿Quién pagó? </label>
            <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                <button
                    v-for="participant in participants"
                    :key="participant.id"
                    type="button"
                    @click="form.payer_id = participant.id"
                    :class="[
                        'touch-target flex shrink-0 flex-col items-center gap-1 rounded-xl p-2 transition-all',
                        form.payer_id === participant.id ? 'bg-secondary-500/20 ring-2 ring-secondary-500' : 'bg-white/5 hover:bg-white/10',
                    ]"
                >
                    <Avatar :name="participant.name" size="sm" />
                    <span class="max-w-[50px] truncate text-xs text-white">
                        {{ participant.name }}
                    </span>
                </button>
            </div>
            <p v-if="form.errors.payer_id" class="mt-1 text-sm text-red-400">
                {{ form.errors.payer_id }}
            </p>
        </div>

        <!-- Split between -->
        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-slate-400"> Dividir entre </label>
                <span v-if="form.amount" class="text-xs text-slate-500"> {{ formatCurrency(equalSplitAmount) }} c/u </span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="participant in participants"
                    :key="participant.id"
                    type="button"
                    @click="toggleParticipant(participant.id)"
                    :class="[
                        'touch-target flex items-center gap-2 rounded-xl px-3 py-2 transition-all',
                        selectedParticipants.has(participant.id) ? 'bg-primary-500/20 ring-1 ring-primary-500/50' : 'bg-white/5 opacity-50',
                    ]"
                >
                    <Avatar :name="participant.name" size="sm" />
                    <span class="text-sm text-white">
                        {{ participant.name }}
                    </span>
                    <svg v-if="selectedParticipants.has(participant.id)" class="h-4 w-4 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
            <p v-if="form.errors.splits" class="mt-1 text-sm text-red-400">
                {{ form.errors.splits }}
            </p>
        </div>

        <!-- Submit -->
        <Button type="submit" :loading="form.processing" :disabled="!form.description || !form.amount || form.amount <= 0" full-width size="lg">
            {{ buttonText }}
        </Button>
    </form>
</template>
