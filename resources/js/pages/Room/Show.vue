<script setup lang="ts">
import AddParticipantModal from '@/components/room/AddParticipantModal.vue';
import BalanceSummary from '@/components/room/BalanceSummary.vue';
import ExpenseCard from '@/components/room/ExpenseCard.vue';
import ExpenseForm from '@/components/room/ExpenseForm.vue';
import ParticipantList from '@/components/room/ParticipantList.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import { useRoomChannel } from '@/composables/useRoomChannel';
import type { Expense, RoomShowProps } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, toRef } from 'vue';

const props = defineProps<RoomShowProps>();

// Subscribe to real-time updates
useRoomChannel({
    room: toRef(() => props.room),
});

// Modal states
const showExpenseModal = ref(false);
const showAddParticipantModal = ref(false);
const editingExpense = ref<Expense | null>(null);

// Check if current user is admin
const isAdmin = computed(() => props.currentParticipant.role === 'admin');

// Check if user can edit a specific expense
const canEditExpense = (expense: Expense) => {
    return isAdmin.value || expense.payer_id === props.currentParticipant.id;
};

// Copy room link
const copied = ref(false);
const copyLink = async () => {
    const url = window.location.href;
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // Fallback for older browsers
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
};

// Handle expense added/updated
const handleExpenseSuccess = () => {
    showExpenseModal.value = false;
    editingExpense.value = null;
};

// Handle edit expense
const handleEditExpense = (expense: Expense) => {
    editingExpense.value = expense;
    showExpenseModal.value = true;
};

// Handle delete expense
const handleDeleteExpense = (expense: Expense) => {
    if (confirm(`¿Seguro que querés eliminar "${expense.description}"?`)) {
        router.delete(`/${props.room.code}/expenses/${expense.id}`);
    }
};

// Modal title
const expenseModalTitle = computed(() => (editingExpense.value ? 'Editar gasto' : 'Nuevo gasto'));

// Sorted expenses (newest first)
const sortedExpenses = computed(() => {
    return [...(props.room.expenses || [])].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
});
</script>

<template>
    <Head :title="`Sala ${room.code}`" />

    <div class="safe-top flex min-h-dvh flex-col">
        <!-- Header -->
        <header class="glass-light sticky top-0 z-40 px-4 py-3">
            <div class="mx-auto flex max-w-2xl items-center justify-between gap-3">
                <!-- Room code -->
                <button
                    @click="copyLink"
                    class="touch-target flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 transition-colors hover:bg-white/10"
                >
                    <span class="font-mono font-bold tracking-wider text-primary-400">
                        {{ room.code }}
                    </span>
                    <svg
                        :class="['h-4 w-4 transition-colors', copied ? 'text-secondary-400' : 'text-slate-400']"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="!copied"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                        />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>

                <!-- Current user -->
                <div class="flex items-center gap-2 text-sm text-slate-400">
                    <span class="max-w-[100px] truncate">{{ currentParticipant.name }}</span>
                    <span
                        v-if="currentParticipant.role === 'admin'"
                        class="rounded-full bg-primary-500/20 px-1.5 py-0.5 text-[10px] font-medium text-primary-400"
                    >
                        Admin
                    </span>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="mx-auto w-full max-w-2xl flex-1 px-4 py-4 pb-24">
            <!-- Participants -->
            <section class="animate-fade-in mb-6">
                <h2 class="mb-2 text-sm font-medium text-slate-400">Participantes</h2>
                <ParticipantList
                    :participants="room.participants || []"
                    :current-participant-id="currentParticipant.id"
                    :is-admin="isAdmin"
                    @add-participant="showAddParticipantModal = true"
                />
            </section>

            <!-- Balance summary -->
            <section class="animate-slide-up mb-6">
                <BalanceSummary :participants="room.participants || []" :expenses="room.expenses || []" />
            </section>

            <!-- Expenses -->
            <section class="animate-slide-up delay-75">
                <h2 class="mb-3 text-sm font-medium text-slate-400">
                    Gastos
                    <span v-if="room.expenses?.length" class="text-slate-500"> ({{ room.expenses.length }}) </span>
                </h2>

                <!-- Empty state -->
                <Card v-if="!room.expenses?.length" padding="lg" class="text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-primary-500/10">
                        <svg class="h-7 w-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <p class="mb-1 text-slate-400">Sin gastos todavía</p>
                    <p class="text-sm text-slate-500">Tocá el botón + para agregar uno</p>
                </Card>

                <!-- Expenses list -->
                <div v-else class="space-y-3">
                    <ExpenseCard
                        v-for="expense in sortedExpenses"
                        :key="expense.id"
                        :expense="expense"
                        :can-edit="canEditExpense(expense)"
                        @edit="handleEditExpense"
                        @delete="handleDeleteExpense"
                    />
                </div>
            </section>
        </main>

        <!-- FAB - Add expense -->
        <button
            @click="showExpenseModal = true"
            class="safe-bottom fixed right-6 bottom-6 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/30 transition-transform active:scale-95"
            aria-label="Agregar gasto"
        >
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </button>

        <!-- Expense modal -->
        <Modal v-model:open="showExpenseModal" :title="expenseModalTitle" @close="editingExpense = null">
            <ExpenseForm
                :room="room"
                :participants="room.participants || []"
                :current-participant="currentParticipant"
                :expense="editingExpense || undefined"
                @success="handleExpenseSuccess"
            />
        </Modal>

        <!-- Add participant modal (admin only) -->
        <AddParticipantModal v-model:open="showAddParticipantModal" :room="room" />
    </div>
</template>
