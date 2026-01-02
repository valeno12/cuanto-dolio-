<script setup lang="ts">
import AddParticipantModal from '@/components/room/AddParticipantModal.vue';
import ParticipantList from '@/components/room/ParticipantList.vue';
import Avatar from '@/components/ui/Avatar.vue';
import Card from '@/components/ui/Card.vue';
import { type Participant, type Room } from '@/types';
import { ref } from 'vue';

const props = defineProps<{
    room: Room;
    currentParticipant: Participant;
    isAdmin: boolean;
    isLocked: boolean;
}>();

const showAddParticipantModal = ref(false);
const editingAlias = ref(false);
const aliasInput = ref(props.currentParticipant.payment_alias || '');
const isSavingAlias = ref(false);

const saveAlias = async () => {
    isSavingAlias.value = true;
    try {
        await fetch(`/${props.room.code}/participants/alias`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ payment_alias: aliasInput.value || null }),
        });
        editingAlias.value = false;
        // In a real app we'd emit update or reload, but layout will handle it via reload usually
        location.reload(); 
    } catch {
        alert('Error al guardar alias');
    } finally {
        isSavingAlias.value = false;
    }
};

const copyLink = async () => {
    await navigator.clipboard.writeText(window.location.href);
    alert('Link copiado!');
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
                    <div v-else class="mt-1 flex items-center gap-2" @click="editingAlias = true">
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
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">CÓDIGO</p>
                        <p class="font-mono text-2xl font-bold tracking-wider text-white">{{ room.code }}</p>
                    </div>
                    <button @click="copyLink" class="rounded-lg bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/20">
                        Copiar Link
                    </button>
                </div>
                
                <div v-if="isLocked" class="mt-4 rounded-lg bg-red-500/20 p-3 text-center text-red-400">
                    🔒 Sala Cerrada
                </div>
            </Card>
        </section>

        <!-- Participants -->
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-medium text-slate-400">Participantes</h3>
                <button 
                    v-if="isAdmin && !isLocked" 
                    @click="showAddParticipantModal = true"
                    class="text-sm text-primary-400 hover:underline"
                >
                    + Agregar
                </button>
            </div>
            
            <ParticipantList
                :participants="room.participants || []"
                :current-participant-id="currentParticipant.id"
                :is-admin="isAdmin"
            />
        </section>

        <!-- Add Participant Modal -->
        <AddParticipantModal v-if="!isLocked" v-model:open="showAddParticipantModal" :room="room" />
    </div>
</template>
