<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Card from '@/components/ui/Card.vue';
import type { Expense } from '@/types';
import { computed, ref } from 'vue';

interface Props {
    expense: Expense;
    canEdit?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    edit: [expense: Expense];
    delete: [expense: Expense];
}>();

const showMenu = ref(false);

const formatCurrency = (amount: string | number): string => {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(num);
};

const formattedDate = computed(() => {
    const date = new Date(props.expense.created_at);
    return new Intl.DateTimeFormat('es-AR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
});

const splitCount = computed(() => props.expense.splits?.length || 0);

const handleEdit = () => {
    showMenu.value = false;
    emit('edit', props.expense);
};

const handleDelete = () => {
    showMenu.value = false;
    emit('delete', props.expense);
};
</script>

<template>
    <Card padding="sm" class="animate-fade-in">
        <div class="flex items-start gap-3">
            <!-- Payer avatar -->
            <Avatar v-if="expense.payer" :name="expense.payer.name" size="md" />

            <!-- Content -->
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="truncate font-medium text-white">
                            {{ expense.description }}
                        </p>
                        <p class="text-sm text-slate-400">
                            <span class="text-secondary-400">{{ expense.payer?.name }}</span>
                            pagó
                        </p>
                    </div>

                    <div class="flex items-start gap-2">
                        <div class="shrink-0 text-right">
                            <p class="font-bold text-white">
                                {{ formatCurrency(expense.amount) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ formattedDate }}
                            </p>
                        </div>

                        <!-- Actions menu -->
                        <div v-if="canEdit" class="relative">
                            <button @click="showMenu = !showMenu" class="touch-target p-1 text-slate-500 transition-colors hover:text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>

                            <!-- Dropdown menu -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div v-if="showMenu" class="glass absolute top-8 right-0 z-10 w-36 rounded-xl py-1 shadow-xl" @click.stop>
                                    <button
                                        @click="handleEdit"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-white transition-colors hover:bg-white/10"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                        Editar
                                    </button>
                                    <button
                                        @click="handleDelete"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-400 transition-colors hover:bg-white/10"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                            </Transition>

                            <!-- Click outside to close -->
                            <div v-if="showMenu" class="fixed inset-0 z-0" @click="showMenu = false" />
                        </div>
                    </div>
                </div>

                <!-- Splits preview -->
                <div v-if="splitCount > 0" class="mt-2 border-t border-white/5 pt-2">
                    <div class="flex items-center gap-1 text-xs text-slate-500">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span>Dividido entre {{ splitCount }} personas</span>
                    </div>
                </div>
            </div>
        </div>
    </Card>
</template>
