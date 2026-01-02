<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import Card from '@/components/ui/Card.vue';
import { computed, onMounted, ref } from 'vue';

interface SettlementTo {
    id: string;
    to: {
        id: string;
        name: string;
        payment_alias: string | null;
    };
    amount: number;
    is_paid: boolean;
    payment_method: string | null;
}

interface SettlementFrom {
    id: string;
    from: {
        id: string;
        name: string;
        is_virtual?: boolean;
    };
    amount: number;
    is_paid: boolean;
    payment_method: string | null;
}

interface VirtualSettlement {
    id: string;
    from: { id: string; name: string };
    to: { id: string; name: string; payment_alias: string | null };
    amount: number;
    is_paid: boolean;
    payment_method: string | null;
}

interface Props {
    roomCode: string;
    isAdmin: boolean;
}

const props = defineProps<Props>();

const loading = ref(true);
const error = ref<string | null>(null);
const iOwe = ref<SettlementTo[]>([]);
const theyOweMe = ref<SettlementFrom[]>([]);
const virtualSettlements = ref<VirtualSettlement[]>([]);
const totalIOwe = ref(0);
const totalOwedToMe = ref(0);

// Payment modal state
const showPaymentModal = ref(false);
const payingSettlement = ref<SettlementTo | VirtualSettlement | null>(null);
const paymentMethod = ref<'cash' | 'transfer'>('transfer');
const isSubmitting = ref(false);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const fetchSettlements = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(`/${props.roomCode}/settlements/my`);
        if (!response.ok) throw new Error('Error al cargar pagos');
        
        const data = await response.json();
        iOwe.value = data.i_owe;
        theyOweMe.value = data.they_owe_me;
        virtualSettlements.value = data.virtual_settlements || [];
        totalIOwe.value = data.total_i_owe;
        totalOwedToMe.value = data.total_owed_to_me;
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Error desconocido';
    } finally {
        loading.value = false;
    }
};

const openPaymentModal = (settlement: SettlementTo | VirtualSettlement) => {
    payingSettlement.value = settlement;
    showPaymentModal.value = true;
};

const confirmPayment = async () => {
    if (!payingSettlement.value) return;
    
    isSubmitting.value = true;
    try {
        const response = await fetch(
            `/${props.roomCode}/settlements/${payingSettlement.value.id}/pay`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ payment_method: paymentMethod.value }),
            }
        );
        
        if (!response.ok) throw new Error('Error al registrar pago');
        
        showPaymentModal.value = false;
        payingSettlement.value = null;
        await fetchSettlements();
    } catch (err) {
        alert('Error al registrar el pago');
    } finally {
        isSubmitting.value = false;
    }
};

const copyAlias = async (alias: string) => {
    await navigator.clipboard.writeText(alias);
    alert('Alias copiado');
};

const shareWhatsApp = async () => {
    const response = await fetch(`/${props.roomCode}/settlements`);
    const data = await response.json();
    
    let text = `💸 *Resumen CuantoDolio - Sala ${props.roomCode}*\n\n`;
    
    data.settlements.forEach((s: { from: string; to: string; amount: number; is_paid: boolean }) => {
        const status = s.is_paid ? '✅' : '🔴';
        text += `${status} ${s.from} → ${s.to}: ${formatCurrency(s.amount)}\n`;
    });
    
    text += `\n*Total gastado: ${formatCurrency(data.total_expenses)}*`;
    text += `\n\n🔗 ${window.location.origin}/${props.roomCode}`;
    
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
};

defineExpose({ fetchSettlements });
onMounted(fetchSettlements);
</script>

<template>
    <div class="space-y-6 pb-24">
        <div v-if="loading" class="flex justify-center py-8">
            <div class="h-8 w-8 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></div>
        </div>

        <template v-else>
            <!-- Hero Status Card -->
            <div class="rounded-3xl bg-gradient-to-br from-slate-800 to-slate-900 p-6 shadow-xl border border-white/5">
                <div class="mb-4 text-center">
                    <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Tu Balance</p>
                    <div v-if="totalIOwe > 0">
                        <h2 class="text-4xl font-bold text-red-500">{{ formatCurrency(totalIOwe) }}</h2>
                        <p class="text-slate-400 mt-1">Tenés que pagar</p>
                    </div>
                    <div v-else-if="totalOwedToMe > 0">
                        <h2 class="text-4xl font-bold text-green-500">{{ formatCurrency(totalOwedToMe) }}</h2>
                        <p class="text-slate-400 mt-1">Te deben</p>
                    </div>
                    <div v-else>
                        <h2 class="text-4xl font-bold text-secondary-500">🥳</h2>
                        <p class="text-slate-400 mt-1">Estás al día</p>
                    </div>
                </div>

                <!-- Primary Action if I owe -->
                <div v-if="iOwe.length > 0" class="mt-6 flex flex-col gap-3">
                    <div 
                        v-for="settlement in iOwe" 
                        :key="settlement.id"
                        class="rounded-xl bg-white/5 p-4"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <Avatar :name="settlement.to.name" size="md" />
                                <div>
                                    <p class="font-medium text-white">{{ settlement.to.name }}</p>
                                    <div v-if="settlement.to.payment_alias" class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 font-mono">{{ settlement.to.payment_alias }}</span>
                                        <button @click="copyAlias(settlement.to.payment_alias!)" class="text-xs text-primary-400">Copiar</button>
                                    </div>
                                </div>
                            </div>
                            <span class="font-bold text-white">{{ formatCurrency(settlement.amount) }}</span>
                        </div>
                        
                        <button
                            v-if="!settlement.is_paid"
                            @click="openPaymentModal(settlement)"
                            class="w-full rounded-lg bg-primary-500 py-3 font-medium text-white shadow-lg shadow-primary-500/25 active:scale-95 transition-all"
                        >
                            Pagar ahora
                        </button>
                        <div v-else class="text-center rounded-lg bg-green-500/20 py-2 text-sm font-medium text-green-400">
                            ✅ Pagado ({{ settlement.payment_method === 'cash' ? 'Efectivo' : 'Transferencia' }})
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Transactions List -->
            <div v-if="theyOweMe.length > 0 || virtualSettlements.length > 0" class="space-y-4">
                <h3 class="font-medium text-slate-400 px-2">Otros Movimientos</h3>
                
                <!-- They Owe Me -->
                <div class="space-y-3">
                    <div 
                        v-for="settlement in theyOweMe" 
                        :key="settlement.id"
                        class="relative overflow-hidden rounded-2xl border border-slate-700 bg-slate-800/50 p-4 transition-all hover:bg-slate-800"
                    >
                         <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-3">
                                <Avatar :name="settlement.from.name" size="md" />
                                <div>
                                    <p class="font-bold text-white leading-tight">{{ settlement.from.name }}</p>
                                    <p class="text-xs text-slate-400">te debe</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-white">{{ formatCurrency(settlement.amount) }}</p>
                                <span v-if="settlement.is_paid" class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-[10px] font-bold text-green-400 uppercase tracking-wide">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span> Pagado
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-500 uppercase tracking-wide">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pendiente
                                </span>
                            </div>
                        </div>
                        <!-- Background decoration -->
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-gradient-to-br from-secondary-500 to-primary-500 opacity-5 blur-xl"></div>
                    </div>
                </div>

                <!-- Virtual (Admin Management) -->
                <template v-if="isAdmin && virtualSettlements.length > 0">
                     <div 
                        v-for="settlement in virtualSettlements" 
                        :key="settlement.id"
                        class="rounded-xl border border-dashed border-slate-700 bg-slate-800/50 p-4"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2 text-sm text-slate-300">
                                <span>{{ settlement.from.name }}</span>
                                <span class="text-slate-500">→</span>
                                <span>{{ settlement.to.name }}</span>
                            </div>
                            <span class="font-bold text-white">{{ formatCurrency(settlement.amount) }}</span>
                        </div>
                        <button
                            v-if="!settlement.is_paid"
                            @click="openPaymentModal(settlement)"
                            class="w-full rounded-lg bg-purple-500/20 py-2 text-xs font-medium text-purple-400 hover:bg-purple-500/30"
                        >
                            Marcar como pagado
                        </button>
                    </div>
                </template>
            </div>

            <!-- Share Button -->
            <button
                @click="shareWhatsApp"
                class="w-full rounded-xl bg-[#25D366] py-4 font-bold text-white shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2"
            >
                <span>📲</span> Compartir en WhatsApp
            </button>
        </template>

        <!-- Payment Method Modal -->
        <Teleport to="body">
            <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                <div class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
                    <h3 class="mb-6 text-xl font-bold text-white text-center">Confirmar Pago</h3>
                    
                    <div class="space-y-3 mb-8">
                        <button
                            @click="paymentMethod = 'transfer'"
                            :class="['w-full flex items-center gap-4 rounded-xl border p-4 transition-all', paymentMethod === 'transfer' ? 'border-primary-500 bg-primary-500/10' : 'border-slate-700 hover:border-slate-600']"
                        >
                            <span class="text-2xl">🏦</span>
                            <div class="text-left">
                                <p class="font-medium text-white">Transferencia</p>
                                <p class="text-xs text-slate-400">Baneconomía, MercadoPago, etc.</p>
                            </div>
                        </button>

                        <button
                            @click="paymentMethod = 'cash'"
                            :class="['w-full flex items-center gap-4 rounded-xl border p-4 transition-all', paymentMethod === 'cash' ? 'border-primary-500 bg-primary-500/10' : 'border-slate-700 hover:border-slate-600']"
                        >
                            <span class="text-2xl">💵</span>
                            <div class="text-left">
                                <p class="font-medium text-white">Efectivo</p>
                                <p class="text-xs text-slate-400">Plata en mano</p>
                            </div>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            @click="showPaymentModal = false"
                            class="rounded-xl bg-slate-800 py-3 font-medium text-slate-300 hover:bg-slate-700"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="confirmPayment"
                            :disabled="isSubmitting"
                            class="rounded-xl bg-white py-3 font-bold text-slate-900 hover:bg-slate-100 disabled:opacity-50"
                        >
                            {{ isSubmitting ? '...' : 'Confirmar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
