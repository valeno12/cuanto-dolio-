<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';
import type { Participant, Room } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    open: boolean;
    participant: Participant | null;
    room: Room;
    currentParticipantId: string;
    isAdmin: boolean;
    isLocked: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// Format currency
const formatCurrency = (amount: string | number): string => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
    }).format(num);
};

// Format date
const formatDate = (dateStr: string): string => {
    return new Intl.DateTimeFormat('es-AR', {
        day: 'numeric',
        month: 'short',
    }).format(new Date(dateStr));
};

// Get role label
const roleLabel = computed(() => {
    if (!props.participant) return '';
    switch (props.participant.role) {
        case 'admin':
            return 'Admin';
        case 'virtual':
            return 'Virtual';
        default:
            return 'Miembro';
    }
});

// Expenses paid by this participant
const expensesPaid = computed(() => {
    if (!props.participant || !props.room.expenses) return [];
    return props.room.expenses.filter((e) => e.payer_id === props.participant!.id);
});

// Total paid
const totalPaid = computed(() => {
    return expensesPaid.value.reduce((sum, e) => sum + parseFloat(e.amount), 0);
});

// What this participant owes (from expense splits)
const owes = computed(() => {
    if (!props.participant || !props.room.expenses) return [];

    const debts: { to: Participant; amount: number }[] = [];

    props.room.expenses.forEach((expense) => {
        if (!expense.splits) return;

        const split = expense.splits.find((s) => s.participant_id === props.participant!.id);
        if (split && expense.payer_id !== props.participant!.id) {
            const payer = props.room.participants?.find((p) => p.id === expense.payer_id);
            if (payer) {
                const existing = debts.find((d) => d.to.id === payer.id);
                if (existing) {
                    existing.amount += parseFloat(split.amount_owed);
                } else {
                    debts.push({ to: payer, amount: parseFloat(split.amount_owed) });
                }
            }
        }
    });

    return debts.filter((d) => d.amount > 0.01);
});

// Total owed
const totalOwed = computed(() => {
    return owes.value.reduce((sum, d) => sum + d.amount, 0);
});

// Can delete: admin can delete non-admin, non-self participants when not locked
const canDelete = computed(() => {
    if (!props.participant) return false;
    if (props.isLocked) return false;
    if (!props.isAdmin) return false;
    if (props.participant.id === props.currentParticipantId) return false;
    if (props.participant.role === 'admin') return false;
    return true;
});

// Is current user
const isCurrentUser = computed(() => {
    return props.participant?.id === props.currentParticipantId;
});

// Handle delete
const handleDelete = () => {
    if (!props.participant) return;
    if (confirm(`¿Seguro que querés eliminar a ${props.participant.name}? Se eliminarán también sus gastos.`)) {
        router.delete(`/${props.room.code}/participants/${props.participant.id}`, {
            onSuccess: () => emit('update:open', false),
        });
    }
};

const close = () => emit('update:open', false);
</script>

<template>
    <Modal :open="open" :title="participant?.name || 'Participante'" @close="close">
        <div v-if="participant" class="space-y-5">
            <!-- Header -->
            <div class="flex items-center gap-4 border-b border-white/10 pb-4">
                <Avatar :name="participant.name" size="xl" />
                <div>
                    <h3 class="text-lg font-bold text-white">{{ participant.name }}</h3>
                    <div class="mt-1 flex items-center gap-2">
                        <span
                            :class="[
                                'rounded-full px-2 py-0.5 text-xs',
                                participant.role === 'admin'
                                    ? 'bg-primary-500/20 text-primary-400'
                                    : participant.role === 'virtual'
                                      ? 'bg-slate-500/20 text-slate-400'
                                      : 'bg-secondary-500/20 text-secondary-400',
                            ]"
                        >
                            {{ roleLabel }}
                        </span>
                        <span v-if="isCurrentUser" class="text-xs text-slate-500">(Vos)</span>
                    </div>
                    <p v-if="participant.payment_alias" class="mt-1 text-xs text-slate-500">
                        {{ participant.payment_alias }}
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-800/50 p-3 text-center">
                    <p class="text-[10px] tracking-wider text-slate-500 uppercase">Pagó</p>
                    <p class="text-lg font-bold text-white">{{ formatCurrency(totalPaid) }}</p>
                </div>
                <div class="rounded-xl bg-slate-800/50 p-3 text-center">
                    <p class="text-[10px] tracking-wider text-slate-500 uppercase">Debe</p>
                    <p class="text-lg font-bold" :class="totalOwed > 0 ? 'text-red-400' : 'text-green-400'">
                        {{ formatCurrency(totalOwed) }}
                    </p>
                </div>
            </div>

            <!-- Expenses paid -->
            <div v-if="expensesPaid.length > 0">
                <h4 class="mb-2 text-xs font-medium tracking-wider text-slate-400 uppercase">📝 Gastos pagados ({{ expensesPaid.length }})</h4>
                <div class="max-h-32 space-y-2 overflow-y-auto">
                    <div
                        v-for="expense in expensesPaid"
                        :key="expense.id"
                        class="flex items-center justify-between rounded-lg bg-slate-800/30 px-3 py-2 text-sm"
                    >
                        <span class="truncate text-slate-300">{{ expense.description }}</span>
                        <span class="ml-2 shrink-0 font-medium text-white">{{ formatCurrency(expense.amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Owes -->
            <div v-if="owes.length > 0">
                <h4 class="mb-2 text-xs font-medium tracking-wider text-slate-400 uppercase">💸 Debe a</h4>
                <div class="space-y-2">
                    <div v-for="debt in owes" :key="debt.to.id" class="flex items-center justify-between rounded-lg bg-red-500/10 px-3 py-2 text-sm">
                        <div class="flex items-center gap-2">
                            <Avatar :name="debt.to.name" size="xs" />
                            <span class="text-slate-300">{{ debt.to.name }}</span>
                        </div>
                        <span class="font-medium text-red-400">{{ formatCurrency(debt.amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- No data -->
            <div v-if="expensesPaid.length === 0 && owes.length === 0" class="py-4 text-center">
                <p class="text-sm text-slate-500">Sin actividad todavía</p>
            </div>

            <!-- Delete button -->
            <div v-if="canDelete" class="border-t border-white/10 pt-4">
                <Button variant="ghost" @click="handleDelete" class="w-full text-red-400 hover:bg-red-500/10"> 🗑️ Eliminar participante </Button>
            </div>
        </div>
    </Modal>
</template>
