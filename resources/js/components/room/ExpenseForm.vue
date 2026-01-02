<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { CATEGORIES, getCategory } from '@/constants/categories'; // Assume alias setup or relative path
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

// Form Setup
const form = useForm({
    description: props.expense?.description || '',
    amount: props.expense ? parseFloat(props.expense.amount) : (null as number | null),
    category: props.expense?.category || 'other',
    payer_id: props.expense?.payer_id || props.currentParticipant.id,
    splits: [] as { participant_id: string; amount: number }[],
});

// Category Selection Logic
const categoryKeys = Object.keys(CATEGORIES) as (keyof typeof CATEGORIES)[];
const selectCategory = (key: string) => {
    form.category = key;
};

// Split Logic
const selectedParticipants = ref<Set<string>>(new Set());

// Initialize splits
const initSplits = () => {
    if (props.expense?.splits?.length) {
        selectedParticipants.value = new Set(props.expense.splits.map((s) => s.participant_id));
    } else {
        selectedParticipants.value = new Set(props.participants.map((p) => p.id));
    }
};
initSplits();

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

// Calculate per-person amount
const equalSplitAmount = computed(() => {
    if (!form.amount || selectedParticipants.value.size === 0) return 0;
    return Math.round((form.amount / selectedParticipants.value.size) * 100) / 100;
});

// Watchers
watch(
    [() => form.amount, selectedParticipants],
    () => {
        form.splits = Array.from(selectedParticipants.value).map((id) => ({
            participant_id: id,
            amount: equalSplitAmount.value,
        }));
    },
    { immediate: true }
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
                initSplits();
            }
            emit('success');
        },
    });
};

const buttonText = computed(() => (isEditing.value ? 'Guardar Cambios' : 'Agregar Gasto'));
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        
        <!-- Inputs Group -->
        <div class="space-y-4">
             <Input v-model="form.description" label="Descripción" :error="form.errors.description" placeholder="Ej: Supermercado" />
             
             <div class="relative">
                <label class="block text-sm font-medium text-slate-400 mb-1">Monto</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold">$</span>
                    <input 
                        v-model="form.amount" 
                        type="number" 
                        inputmode="decimal" 
                        step="0.01" 
                        class="w-full rounded-xl border-none bg-slate-800 py-3 pl-8 pr-4 text-white placeholder-slate-500 ring-1 ring-slate-700/50 focus:ring-2 focus:ring-primary-500"
                        placeholder="0.00"
                    />
                </div>
                <p v-if="form.errors.amount" class="mt-1 text-xs text-red-500">{{ form.errors.amount }}</p>
             </div>
        </div>

        <!-- Category Selector -->
        <div>
             <label class="mb-3 block text-sm font-medium text-slate-400">Categoría</label>
             <div class="grid grid-cols-4 gap-2">
                 <button
                    v-for="key in categoryKeys"
                    :key="key"
                    type="button"
                    @click="selectCategory(key)"
                    :class="[
                        'flex flex-col items-center justify-center rounded-xl p-2 transition-all border',
                        form.category === key 
                            ? `border-${CATEGORIES[key].color.replace('bg-', '')} bg-white/10 ring-1 ring-white/20` 
                            : 'border-transparent bg-slate-800/50 hover:bg-slate-800'
                    ]"
                 >
                    <span class="text-2xl mb-1">{{ CATEGORIES[key].icon }}</span>
                    <span :class="['text-[10px] font-medium', form.category === key ? 'text-white' : 'text-slate-500']">
                        {{ CATEGORIES[key].label }}
                    </span>
                 </button>
             </div>
        </div>

        <!-- Payer Selection -->
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-400">¿Quién pagó?</label>
            <div class="no-scrollbar flex gap-2 overflow-x-auto pb-2">
                <button
                    v-for="participant in participants"
                    :key="participant.id"
                    type="button"
                    @click="form.payer_id = participant.id"
                    :class="[
                        'flex items-center gap-2 rounded-full px-3 py-1.5 transition-all border',
                        form.payer_id === participant.id 
                            ? 'bg-primary-500 text-white border-primary-400' 
                            : 'bg-slate-800 text-slate-400 border-slate-700 hover:border-slate-600'
                    ]"
                >
                    <Avatar :name="participant.name" size="xs" :show-name="false" />
                    <span class="text-xs font-medium">{{ participant.name }}</span>
                </button>
            </div>
        </div>

        <!-- Split Selection -->
        <div class="rounded-xl bg-slate-800/30 p-4 border border-slate-700/50">
            <div class="mb-3 flex items-center justify-between">
                <label class="text-sm font-medium text-slate-400">Dividir entre</label>
                <span v-if="form.amount" class="text-xs font-bold text-primary-400">
                    {{ formatCurrency(equalSplitAmount) }} c/u
                </span>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="participant in participants"
                    :key="participant.id"
                    type="button"
                    @click="toggleParticipant(participant.id)"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3 py-2 transition-all border',
                        selectedParticipants.has(participant.id) 
                            ? 'bg-slate-700 border-slate-500 text-white' 
                            : 'bg-slate-800 border-transparent text-slate-500 opacity-60'
                    ]"
                >
                     <Avatar :name="participant.name" size="xs" :class="!selectedParticipants.has(participant.id) ? 'grayscale opacity-50' : ''" />
                     <span class="text-xs font-medium">{{ participant.name }}</span>
                </button>
            </div>
        </div>

        <Button type="submit" :loading="form.processing" :disabled="!form.description || !form.amount || form.amount <= 0" full-width size="lg" class="shadow-lg shadow-primary-500/20">
            {{ buttonText }}
        </Button>
    </form>
</template>
