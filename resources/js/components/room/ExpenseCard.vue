<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import { getCategory } from '@/constants/categories';
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
    }).format(date);
});

const categoryDef = computed(() => getCategory(props.expense.category));

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
    <div
        class="group relative cursor-pointer overflow-visible rounded-2xl border border-slate-800 bg-[#161b26] p-4 transition-all hover:border-slate-700 hover:bg-[#1a202e] active:scale-[0.98]"
    >
        <div class="flex items-start gap-3">
            <!-- Category Icon -->
            <div
                :class="[
                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/5 text-xl',
                    categoryDef.color,
                    'bg-opacity-20',
                ]"
            >
                {{ categoryDef.icon }}
            </div>

            <!-- Content -->
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-start justify-between gap-2">
                    <p class="truncate text-sm leading-tight font-medium text-white">
                        {{ expense.description }}
                    </p>
                    <div class="flex shrink-0 items-center gap-1">
                        <span class="text-sm font-bold text-white">
                            {{ formatCurrency(expense.amount) }}
                        </span>
                        <!-- Menu Button - inline after amount -->
                        <div v-if="canEdit" class="relative">
                            <button
                                @click.stop="showMenu = !showMenu"
                                class="flex h-6 w-6 items-center justify-center rounded-full text-slate-500 transition-all hover:bg-slate-700 hover:text-white active:scale-90"
                            >
                                ⋮
                            </button>
                            <!-- Menu Dropdown -->
                            <div
                                v-if="showMenu"
                                class="absolute top-7 right-0 z-20 w-28 overflow-hidden rounded-xl bg-slate-800 shadow-xl ring-1 ring-white/10"
                            >
                                <button @click="handleEdit" class="w-full px-3 py-2.5 text-left text-sm text-white hover:bg-white/5">Editar</button>
                                <button @click="handleDelete" class="w-full px-3 py-2.5 text-left text-sm text-red-400 hover:bg-white/5">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-0.5 text-xs text-slate-400">
                    <span class="text-slate-300">{{ expense.payer?.name }}</span>
                    <span class="mx-1 text-slate-600">•</span>
                    {{ categoryDef.label }}
                    <span class="mx-1 text-slate-600">•</span>
                    <span class="text-slate-500">{{ formattedDate }}</span>
                </p>

                <!-- Avatars -->
                <div v-if="expense.splits && expense.splits.length > 0" class="mt-2 flex -space-x-1 overflow-hidden">
                    <Avatar
                        v-for="split in expense.splits.slice(0, 5)"
                        :key="split.id"
                        :name="split.participant?.name || '?'"
                        size="xs"
                        class="h-5 w-5 text-[9px] ring-2 ring-[#161b26]"
                    />
                    <div
                        v-if="expense.splits.length > 5"
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-800 text-[9px] text-slate-400 ring-2 ring-[#161b26]"
                    >
                        +{{ expense.splits.length - 5 }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Backdrop to close menu -->
        <div v-if="showMenu" class="fixed inset-0 z-10" @click="showMenu = false"></div>
    </div>
</template>
