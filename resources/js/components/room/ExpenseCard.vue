<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Card from '@/components/ui/Card.vue';
import { CATEGORIES, getCategory } from '@/constants/categories'; // Assume alias setup
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

// Category Logic
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
    <div class="group relative overflow-visible rounded-2xl border border-slate-800 bg-[#161b26] p-4 transition-all hover:bg-[#1a202e] hover:border-slate-700">
        <div class="flex items-center gap-4">
            <!-- Category Icon -->
            <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-2xl shadow-inner border border-white/5', categoryDef.color, 'bg-opacity-20']">
                {{ categoryDef.icon }}
            </div>

            <!-- Content -->
            <div class="flex min-w-0 flex-1 flex-col justify-center">
                <div class="flex items-baseline justify-between">
                    <p class="truncate font-medium text-white text-base leading-tight">
                        {{ expense.description }}
                    </p>
                    <span class="shrink-0 font-bold text-white text-base">
                        {{ formatCurrency(expense.amount) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between mt-1">
                    <p class="text-xs text-slate-400">
                        <span class="font-medium text-slate-300">{{ expense.payer?.name }}</span> 
                        <span class="text-slate-600 mx-1">•</span>
                        {{ categoryDef.label }}
                    </p>
                    <span class="text-[10px] text-slate-500">
                        {{ formattedDate }}
                    </span>
                </div>

                <!-- Avatars Mini -->
                <div v-if="expense.splits && expense.splits.length > 0" class="mt-2.5 flex -space-x-1.5 overflow-hidden">
                    <Avatar 
                        v-for="split in expense.splits.slice(0, 5)" 
                        :key="split.id"
                        :name="split.participant?.name || '?'" 
                        size="xs"
                        class="ring-2 ring-[#161b26] h-5 w-5 text-[9px]"
                    />
                    <div v-if="expense.splits.length > 5" class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-800 text-[9px] text-slate-400 ring-2 ring-[#161b26]">
                        +{{ expense.splits.length - 5 }}
                    </div>
                </div>
            </div>
            
            <!-- Actions (Absolute positioning for touch targets) -->
            <button 
                v-if="canEdit" 
                @click.stop="showMenu = !showMenu" 
                class="absolute -top-2 -right-2 hidden h-8 w-8 items-center justify-center rounded-full bg-slate-700 text-slate-300 opacity-0 shadow-lg transition-all hover:bg-slate-600 group-hover:flex group-hover:opacity-100 z-10"
            >
                ⋮
            </button>
        </div>

        <!-- Menu Dropdown -->
        <div v-if="showMenu" class="absolute right-2 top-8 z-20 w-32 overflow-hidden rounded-xl bg-slate-800 shadow-xl ring-1 ring-white/10" @click.stop>
            <button @click="handleEdit" class="w-full px-4 py-3 text-left text-sm text-white hover:bg-white/5 active:bg-white/10">Editar</button>
            <button @click="handleDelete" class="w-full px-4 py-3 text-left text-sm text-red-400 hover:bg-white/5 active:bg-white/10">Eliminar</button>
        </div>
        <div v-if="showMenu" class="fixed inset-0 z-10" @click="showMenu = false"></div>
    </div>
</template>
