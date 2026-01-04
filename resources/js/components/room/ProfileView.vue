<script setup lang="ts">
import AddParticipantModal from '@/components/room/AddParticipantModal.vue';
import ParticipantList from '@/components/room/ParticipantList.vue';
import ParticipantProfileModal from '@/components/room/ParticipantProfileModal.vue';
import Avatar from '@/components/ui/Avatar.vue';
import Card from '@/components/ui/Card.vue';
import { useToast } from '@/composables/useToast';
import { type Participant, type Room } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const toast = useToast();

const props = defineProps<{
    room: Room;
    currentParticipant: Participant;
    isAdmin: boolean;
    isLocked: boolean;
}>();

const showAddParticipantModal = ref(false);
const showParticipantProfile = ref(false);
const selectedParticipant = ref<Participant | null>(null);
const editingAlias = ref(false);
const aliasInput = ref(props.currentParticipant.payment_alias || '');
const isSavingAlias = ref(false);

// Room name editing
const editingRoomName = ref(false);
const roomNameInput = ref(props.room.name || '');
const isSavingRoomName = ref(false);

// Days remaining until room expires
const daysRemaining = computed(() => {
    if (!props.room.expires_at) return null;
    const expiresAt = new Date(props.room.expires_at);
    const now = new Date();
    const diffTime = expiresAt.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
});

const saveAlias = () => {
    isSavingAlias.value = true;
    router.put(
        `/${props.room.code}/participants/alias`,
        { payment_alias: aliasInput.value || null },
        {
            preserveScroll: true,
            onSuccess: () => {
                editingAlias.value = false;
                toast.success('Alias guardado');
            },
            onError: () => {
                toast.error('Error al guardar alias');
            },
            onFinish: () => {
                isSavingAlias.value = false;
            },
        },
    );
};

const saveRoomName = () => {
    isSavingRoomName.value = true;
    router.put(
        `/${props.room.code}/name`,
        { name: roomNameInput.value || null },
        {
            preserveScroll: true,
            onSuccess: () => {
                editingRoomName.value = false;
                toast.success('Nombre de sala actualizado');
            },
            onError: () => {
                toast.error('Error al guardar nombre');
            },
            onFinish: () => {
                isSavingRoomName.value = false;
            },
        },
    );
};

const copyLink = async () => {
    await navigator.clipboard.writeText(window.location.href);
    toast.success('Link copiado');
};

const shareRoom = () => {
    const roomName = props.room.name || `Sala ${props.room.code}`;
    const message = `¡Te invito a "${roomName}" en Cuanto Dolió! 💸\n\nDividimos gastos fácil, sin registro ni login.\n\nÚnite acá: ${window.location.href}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
};

const handleSelectParticipant = (participant: Participant) => {
    selectedParticipant.value = participant;
    showParticipantProfile.value = true;
};

const handleReopenRoom = () => {
    if (confirm('¿Seguro que querés reabrir la sala? Los pagos pendientes se recalcularán cuando la vuelvas a cerrar.')) {
        router.post(`/${props.room.code}/unlock`);
    }
};
</script>

<template>
    <div class="space-y-6 pb-24">
        <!-- My Profile Card -->
        <Card>
            <div class="flex items-center gap-4">
                <Avatar :name="currentParticipant.name" size="lg" />
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-white">
                        {{ currentParticipant.name }}
                        <span v-if="isAdmin" class="ml-2 rounded-full bg-primary-500/20 px-2 py-0.5 text-xs text-primary-400">Admin</span>
                    </h2>

                    <!-- Alias Editor -->
                    <div v-if="editingAlias" class="mt-2 flex gap-2">
                        <input
                            v-model="aliasInput"
                            placeholder="CBU / Alias MP"
                            class="w-full rounded-lg bg-slate-700 px-2 py-1 text-sm text-white focus:ring-1 focus:ring-primary-500"
                        />
                        <button @click="saveAlias" :disabled="isSavingAlias" class="text-primary-400">✓</button>
                    </div>
                    <div v-else class="mt-1 flex cursor-pointer items-center gap-2" @click="editingAlias = true">
                        <p class="text-sm text-slate-400">{{ currentParticipant.payment_alias || 'Configurar alias de pago' }}</p>
                        <span class="text-xs text-slate-600">✎</span>
                    </div>
                </div>
            </div>
        </Card>

        <!-- Room Info -->
        <section>
            <h3 class="mb-3 font-medium text-slate-400">Sala</h3>
            <Card>
                <!-- Room Name (admin can edit) -->
                <div v-if="isAdmin" class="mb-4">
                    <div v-if="editingRoomName" class="flex gap-2">
                        <input
                            v-model="roomNameInput"
                            placeholder="Nombre de la sala"
                            class="w-full rounded-lg bg-slate-700 px-3 py-2 text-white focus:ring-1 focus:ring-primary-500"
                        />
                        <button @click="saveRoomName" :disabled="isSavingRoomName" class="px-2 text-primary-400">✓</button>
                        <button @click="editingRoomName = false" class="px-2 text-slate-400">✕</button>
                    </div>
                    <div v-else class="flex cursor-pointer items-center gap-2" @click="editingRoomName = true">
                        <p class="text-lg font-bold text-white">{{ room.name || 'Sin nombre' }}</p>
                        <span class="text-xs text-slate-600">✎</span>
                    </div>
                </div>
                <div v-else-if="room.name" class="mb-4">
                    <p class="text-lg font-bold text-white">{{ room.name }}</p>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">CÓDIGO</p>
                        <p class="font-mono text-2xl font-bold tracking-wider text-white">{{ room.code }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="copyLink" class="rounded-lg bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20">📋 Copiar</button>
                        <button @click="shareRoom" class="rounded-lg bg-green-600/80 px-3 py-2 text-sm text-white hover:bg-green-600">
                            📲 Compartir
                        </button>
                    </div>
                </div>

                <!-- Days remaining -->
                <div v-if="daysRemaining !== null" class="mt-4 flex items-center justify-center gap-2 text-sm text-slate-400">
                    <span>⏱️</span>
                    <span v-if="daysRemaining > 7">{{ daysRemaining }} días restantes</span>
                    <span v-else-if="daysRemaining > 1" class="text-yellow-400">⚠️ {{ daysRemaining }} días restantes</span>
                    <span v-else-if="daysRemaining === 1" class="text-orange-400">⚠️ ¡Último día!</span>
                    <span v-else class="text-red-400">⚠️ Sala vencida</span>
                </div>

                <div v-if="isLocked" class="mt-4 rounded-lg bg-red-500/20 p-3 text-center">
                    <p class="mb-2 text-red-400">🔒 Sala Cerrada</p>
                    <button v-if="isAdmin" @click="handleReopenRoom" class="text-sm text-primary-400 hover:underline">Reabrir sala</button>
                </div>
            </Card>
        </section>

        <!-- Participants -->
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-medium text-slate-400">Participantes</h3>
                <button v-if="isAdmin && !isLocked" @click="showAddParticipantModal = true" class="text-sm text-primary-400 hover:underline">
                    + Agregar
                </button>
            </div>

            <ParticipantList
                :participants="room.participants || []"
                :current-participant-id="currentParticipant.id"
                :is-admin="isAdmin"
                :is-locked="isLocked"
                @add-participant="showAddParticipantModal = true"
                @select-participant="handleSelectParticipant"
            />
        </section>

        <!-- Add Participant Modal -->
        <AddParticipantModal v-if="!isLocked" v-model:open="showAddParticipantModal" :room="room" />

        <!-- Participant Profile Modal -->
        <ParticipantProfileModal
            v-model:open="showParticipantProfile"
            :participant="selectedParticipant"
            :room="room"
            :current-participant-id="currentParticipant.id"
            :is-admin="isAdmin"
            :is-locked="isLocked"
        />
    </div>
</template>
