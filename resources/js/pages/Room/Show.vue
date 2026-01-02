<script setup lang="ts">
import BottomNav from '@/components/room/BottomNav.vue';
import ExpensesView from '@/components/room/ExpensesView.vue';
import PaymentDashboard from '@/components/room/PaymentDashboard.vue';
import ProfileView from '@/components/room/ProfileView.vue';
import { useRoomChannel } from '@/composables/useRoomChannel';
import type { RoomShowProps } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, toRef, watch } from 'vue';

const props = defineProps<RoomShowProps>();

// Tab state
const activeTab = ref<'expenses' | 'settlement' | 'profile'>('expenses');

// Check if current user is admin
const isAdmin = computed(() => props.currentParticipant.role === 'admin');
const isLocked = computed(() => props.room.is_locked);

// Calculate total spent
const totalSpent = computed(() => {
    return props.room.expenses?.reduce((sum, e) => sum + Number(e.amount), 0) || 0;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Update dashboard when payments happen
const paymentDashboard = ref<InstanceType<typeof PaymentDashboard> | null>(null);

useRoomChannel({
    room: toRef(() => props.room),
    onSettlementPaid: () => {
        if (activeTab.value === 'settlement') {
            paymentDashboard.value?.fetchSettlements();
        }
    },
    onRoomLocked: () => {
        // If room gets locked, switch to settlement view to see results
        activeTab.value = 'settlement';
        router.reload({ only: ['room'] });
    },
});

// Watch for locked state change initally
watch(() => props.room.is_locked, (newVal) => {
    if (newVal) activeTab.value = 'settlement';
}, { immediate: true });

// Lock room logic (admin)
const lockingRoom = ref(false);
const handleLockRoom = () => {
    if (!isAdmin.value || isLocked.value) return;
    if (confirm('¿Cerrar la sala y calcular deudas?')) {
        lockingRoom.value = true;
        router.post(`/${props.room.code}/lock`, {}, {
            onFinish: () => {
                lockingRoom.value = false;
                activeTab.value = 'settlement';
            },
        });
    }
};

</script>

<template>
    <Head :title="`Sala ${room.code}`" />

    <div class="min-h-dvh bg-[#090b10] flex justify-center text-white">
        <!-- Desktop Sidebar (Visible lg+) -->
        <aside class="hidden lg:flex fixed left-0 top-0 h-dvh w-64 flex-col border-r border-white/5 bg-[#0f111a] p-6">
             <div class="mb-8 flex items-center gap-3">
                <img src="/images/logo.png" alt="Cuanto Dolio" class="h-14 w-auto drop-shadow-lg" />
             </div>

             <nav class="flex-1 space-y-2">
                <button 
                    v-for="tab in ['expenses', 'settlement', 'profile'] as const"
                    :key="tab"
                    @click="activeTab = tab"
                    :class="[
                        'flex w-full items-center gap-3 rounded-xl px-4 py-3 transition-all',
                        activeTab === tab ? 'bg-primary-500/10 text-primary-400 font-bold' : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    ]"
                >
                    <span class="text-xl">
                        {{ tab === 'expenses' ? '📝' : tab === 'settlement' ? '💸' : '👤' }}
                    </span>
                    <span class="capitalize">{{ tab === 'expenses' ? 'Gastos' : tab === 'settlement' ? 'Pagos' : 'Perfil' }}</span>
                </button>
             </nav>

             <div class="mt-auto rounded-xl bg-slate-800/50 p-4 border border-white/5">
                <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Total Gastado</p>
                <p class="text-2xl font-bold text-white">{{ formatCurrency(totalSpent) }}</p>
             </div>
        </aside>

        <!-- Main Content Area -->
        <div class="w-full lg:pl-64 flex flex-col min-h-dvh transition-all duration-300">
            
            <!-- Mobile/Tablet Header (Hidden on LG) -->
            <header class="sticky top-0 z-40 lg:hidden bg-[#0f111a]/95 px-5 py-4 backdrop-blur-md border-b border-white/5 supports-[backdrop-filter]:bg-[#0f111a]/80">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo.png" alt="Cuanto Dolio" class="h-10 w-auto drop-shadow-lg" />
                    </div>
                    
                    <div v-if="isLocked" class="rounded-full bg-red-500/10 px-3 py-1 text-xs font-bold text-red-400 border border-red-500/20">
                        Cerrada
                    </div>
                    <div v-else class="flex flex-col items-end">
                        <span class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">Total</span>
                        <p class="text-lg font-bold text-white leading-none">{{ formatCurrency(totalSpent) }}</p>
                    </div>
                </div>
            </header>

            <!-- Desktop Top Bar (Room info + Lock button) -->
            <header class="hidden lg:flex sticky top-0 z-40 bg-[#090b10]/95 backdrop-blur-md px-8 py-6 items-center justify-between border-b border-white/5">
                <div>
                    <h1 class="text-2xl font-bold text-white">Sala {{ room.code }}</h1>
                    <p class="text-slate-400 text-sm">Gestioná los gastos de tu grupo</p>
                </div>

                <div class="flex items-center gap-4">
                    <div v-if="isLocked" class="flex items-center gap-2 rounded-full bg-red-500/10 px-4 py-2 text-sm font-bold text-red-400 border border-red-500/20">
                        <span>🔒</span> Sala Cerrada
                    </div>
                    <button 
                         v-else-if="isAdmin && room.expenses?.length && activeTab === 'settlement'"
                         @click="handleLockRoom"
                         class="rounded-xl bg-gradient-to-r from-secondary-500 to-secondary-600 px-6 py-2.5 font-bold text-white shadow-lg shadow-secondary-500/20 hover:scale-105 active:scale-95 transition-all"
                    >
                        🔒 Cerrar Sala y Calcular
                    </button>
                    <!-- Current User badge -->
                    <div class="flex items-center gap-3 pl-6 border-l border-white/5">
                         <div class="text-right">
                            <p class="text-sm font-bold text-white">{{ currentParticipant.name }}</p>
                            <p class="text-xs text-slate-500 capitalize">{{ currentParticipant.role === 'admin' ? 'Administrador' : 'Miembro' }}</p>
                         </div>
                         <div class="h-10 w-10 flex items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-purple-600 text-white font-bold shadow-lg">
                            {{ currentParticipant.name.substring(0,2).toUpperCase() }}
                         </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 w-full max-w-5xl mx-auto px-4 lg:px-8 py-6 lg:py-10 pb-24 lg:pb-10 relative z-0">
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                    mode="out-in"
                >
                    <!-- Expenses Tab -->
                    <ExpensesView 
                        v-if="activeTab === 'expenses'" 
                        :room="room" 
                        :current-participant="currentParticipant"
                        :is-admin="isAdmin"
                        :is-locked="isLocked"
                        class="mx-auto"
                    />

                    <!-- Settlement Tab -->
                    <div v-else-if="activeTab === 'settlement'" class="max-w-3xl mx-auto">
                        <div v-if="!isLocked" class="flex flex-col items-center justify-center py-20 text-center text-slate-400 border-2 border-dashed border-slate-800 rounded-3xl bg-white/5">
                            <span class="text-6xl mb-6 bg-slate-800 rounded-full p-6 shadow-xl">🔒</span>
                            <h3 class="text-2xl font-bold text-white mb-2">La sala está abierta</h3>
                            <p class="text-slate-400 mb-8 max-w-md mx-auto">Cuando terminen de cargar todos los gastos, el administrador puede cerrar la sala para calcular automáticamente quién le debe a quién.</p>
                            
                            <button 
                                v-if="isAdmin && room.expenses?.length"
                                @click="handleLockRoom"
                                class="hidden lg:block rounded-xl bg-gradient-to-r from-secondary-500 to-secondary-600 px-8 py-4 font-bold text-white shadow-xl shadow-secondary-500/20 hover:scale-105 active:scale-95 transition-all"
                            >
                                Cerrar Sala y Calcular
                            </button>
                             <button 
                                v-if="isAdmin && room.expenses?.length"
                                @click="handleLockRoom"
                                class="lg:hidden rounded-xl bg-gradient-to-r from-secondary-500 to-secondary-600 px-6 py-3 font-bold text-white shadow-lg active:scale-95 transition-all"
                            >
                                Cerrar Sala
                            </button>
                        </div>
                        <PaymentDashboard 
                            v-else
                            ref="paymentDashboard"
                            :room-code="room.code" 
                            :is-admin="isAdmin" 
                        />
                    </div>

                    <!-- Profile Tab -->
                    <ProfileView 
                        v-else-if="activeTab === 'profile'"
                        :room="room"
                        :current-participant="currentParticipant"
                        :is-admin="isAdmin"
                        :is-locked="isLocked"
                        class="max-w-xl mx-auto"
                    />
                </Transition>
            </main>
        </div>

        <!-- Mobile Navigation -->
        <BottomNav v-model="activeTab" class="lg:hidden" />
    </div>
</template>
