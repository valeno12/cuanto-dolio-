<script setup lang="ts">
import ExpenseCard from '@/components/room/ExpenseCard.vue';
import ExpenseForm from '@/components/room/ExpenseForm.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import { useRoomChannel } from '@/composables/useRoomChannel';
import type { Expense, Participant, Room } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    room: Room;
    currentParticipant: Participant;
    isAdmin: boolean;
    isLocked: boolean;
}>();

// Modal states
const showExpenseModal = ref(false);
const editingExpense = ref<Expense | null>(null);

// Sorted expenses
const sortedExpenses = computed(() => {
    return [...(props.room.expenses || [])].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
});

// Check permissions
const canEditExpense = (expense: Expense) => {
    if (props.isLocked) return false;
    return props.isAdmin || expense.payer_id === props.currentParticipant.id;
};

// Handlers
const handleExpenseSuccess = () => {
    showExpenseModal.value = false;
    editingExpense.value = null;
};

const handleEditExpense = (expense: Expense) => {
    if (props.isLocked) return;
    editingExpense.value = expense;
    showExpenseModal.value = true;
};

const handleDeleteExpense = (expense: Expense) => {
    if (props.isLocked) return;
    if (confirm(`¿Seguro que querés eliminar "${expense.description}"?`)) {
        router.delete(`/${props.room.code}/expenses/${expense.id}`);
    }
};

const expenseModalTitle = computed(() => (editingExpense.value ? 'Editar gasto' : 'Nuevo gasto'));
</script>

<template>
    <div class="space-y-4 pb-24">
        <!-- New Expense FAB -->
        <!-- New Expense FAB (Mobile/Desktop) -->
        <button
            v-if="!isLocked"
            @click="showExpenseModal = true"
            class="fixed right-4 bottom-20 lg:right-10 lg:bottom-10 z-30 flex h-14 w-14 lg:h-16 lg:w-16 items-center justify-center rounded-full bg-primary-500 text-white shadow-lg shadow-primary-500/30 transition-transform active:scale-95 hover:scale-105"
        >
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </button>

        <!-- Expenses List -->
        <div v-if="sortedExpenses.length > 0" class="grid grid-cols-1 gap-3 lg:grid-cols-2 lg:gap-4 xl:grid-cols-3">
            <ExpenseCard
                v-for="expense in sortedExpenses"
                :key="expense.id"
                :expense="expense"
                :can-edit="canEditExpense(expense)"
                @edit="handleEditExpense"
                @delete="handleDeleteExpense"
            />
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
             <div class="relative mb-6">
                <div class="absolute -inset-4 rounded-full bg-primary-500/20 blur-xl"></div>
                <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-slate-800 shadow-xl ring-1 ring-white/10">
                    <span class="text-5xl">👻</span>
                </div>
             </div>
            <h3 class="text-xl font-bold text-white mb-2">¡Esto está muy tranquilo!</h3>
            <p class="text-slate-400 max-w-[250px] mx-auto text-sm leading-relaxed">
                Todavía no cargaron ningún gasto. Tocá el botón <span class="text-primary-400 font-bold">+</span> para empezar a dividir.
            </p>
        </div>

        <!-- Expense Modal -->
        <Modal v-model:open="showExpenseModal" :title="expenseModalTitle" @close="editingExpense = null">
            <ExpenseForm
                :room="room"
                :participants="room.participants || []"
                :current-participant="currentParticipant"
                :expense="editingExpense || undefined"
                @success="handleExpenseSuccess"
            />
        </Modal>
    </div>
</template>
