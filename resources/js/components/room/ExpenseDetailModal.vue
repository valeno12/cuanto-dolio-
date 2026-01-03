<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';
import { getCategory } from '@/constants/categories';
import type { Expense } from '@/types';
import { computed } from 'vue';

interface Props {
    open: boolean;
    expense: Expense | null;
    canEdit?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    edit: [expense: Expense];
    delete: [expense: Expense];
}>();

const formatCurrency = (amount: string | number): string => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
    }).format(num);
};

const formatDate = (dateStr: string): string => {
    return new Intl.DateTimeFormat('es-AR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(dateStr));
};

const categoryDef = computed(() => (props.expense ? getCategory(props.expense.category) : null));

const close = () => emit('update:open', false);

const handleEdit = () => {
    if (props.expense) {
        emit('edit', props.expense);
        close();
    }
};

const handleDelete = () => {
    if (props.expense) {
        emit('delete', props.expense);
        close();
    }
};
</script>

<template>
    <Modal :open="open" :title="expense?.description || 'Detalle del gasto'" @close="close">
        <div v-if="expense" class="space-y-5">
            <!-- Header with category and amount -->
            <div class="flex items-center gap-4 border-b border-white/10 pb-4">
                <div
                    v-if="categoryDef"
                    :class="['flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-3xl', categoryDef.color, 'bg-opacity-20']"
                >
                    {{ categoryDef.icon }}
                </div>
                <div class="flex-1">
                    <p class="text-2xl font-bold text-white">{{ formatCurrency(expense.amount) }}</p>
                    <p class="text-sm text-slate-400">{{ categoryDef?.label }}</p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <p class="mb-1 text-xs font-medium tracking-wider text-slate-500 uppercase">Descripción</p>
                <p class="text-white">{{ expense.description }}</p>
            </div>

            <!-- Payer -->
            <div>
                <p class="mb-2 text-xs font-medium tracking-wider text-slate-500 uppercase">Pagado por</p>
                <div class="flex items-center gap-3 rounded-xl bg-slate-800/50 p-3">
                    <Avatar :name="expense.payer?.name || '?'" size="md" />
                    <div>
                        <p class="font-medium text-white">{{ expense.payer?.name }}</p>
                        <p v-if="expense.payer?.payment_alias" class="text-xs text-primary-400">
                            {{ expense.payer.payment_alias }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Split -->
            <div>
                <p class="mb-2 text-xs font-medium tracking-wider text-slate-500 uppercase">Dividido entre ({{ expense.splits?.length || 0 }})</p>
                <div class="max-h-48 space-y-2 overflow-y-auto">
                    <div
                        v-for="split in expense.splits"
                        :key="split.id"
                        class="flex items-center justify-between rounded-lg bg-slate-800/30 px-3 py-2"
                    >
                        <div class="flex items-center gap-2">
                            <Avatar :name="split.participant?.name || '?'" size="sm" />
                            <span class="text-sm text-slate-300">{{ split.participant?.name }}</span>
                        </div>
                        <span class="text-sm font-medium text-white">{{ formatCurrency(split.amount_owed) }}</span>
                    </div>
                </div>
            </div>

            <!-- Date -->
            <div class="border-t border-white/10 pt-2">
                <p class="text-center text-xs text-slate-500">
                    {{ formatDate(expense.created_at) }}
                </p>
            </div>

            <!-- Actions -->
            <div v-if="canEdit" class="flex gap-3 pt-2">
                <Button variant="secondary" full-width @click="handleEdit"> ✏️ Editar </Button>
                <Button variant="ghost" full-width @click="handleDelete" class="text-red-400 hover:bg-red-500/10"> 🗑️ Eliminar </Button>
            </div>
        </div>
    </Modal>
</template>
