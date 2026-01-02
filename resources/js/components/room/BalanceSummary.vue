<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Card from '@/components/ui/Card.vue';
import { useBalances } from '@/composables/useBalances';
import type { Expense, Participant } from '@/types';
import { computed } from 'vue';

interface Props {
    participants: Participant[];
    expenses: Expense[];
}

const props = defineProps<Props>();

const { debts, totalExpenses, formatCurrency } = useBalances({
    participants: computed(() => props.participants),
    expenses: computed(() => props.expenses),
});
</script>

<template>
    <Card padding="md">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-semibold text-white">Resumen</h3>
            <span class="text-sm text-slate-400">
                Total: <span class="font-medium text-white">{{ formatCurrency(totalExpenses) }}</span>
            </span>
        </div>

        <!-- No debts -->
        <div v-if="debts.length === 0" class="py-4 text-center">
            <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-secondary-500/20">
                <svg class="h-6 w-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm text-slate-400">
                {{ expenses.length === 0 ? 'Todavía no hay gastos' : '¡Están a mano!' }}
            </p>
        </div>

        <!-- Debts list -->
        <div v-else class="space-y-3">
            <div v-for="(debt, index) in debts" :key="index" class="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                <!-- From -->
                <Avatar :name="debt.from.name" size="sm" />

                <!-- Arrow -->
                <div class="flex flex-1 items-center gap-2">
                    <span class="truncate text-sm text-slate-300">{{ debt.from.name }}</span>
                    <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                    <span class="truncate text-sm text-slate-300">{{ debt.to.name }}</span>
                </div>

                <!-- To -->
                <Avatar :name="debt.to.name" size="sm" />

                <!-- Amount -->
                <span class="shrink-0 font-bold text-red-400">
                    {{ formatCurrency(debt.amount) }}
                </span>
            </div>
        </div>
    </Card>
</template>
