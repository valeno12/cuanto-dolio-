<script setup lang="ts">
import Modal from '@/components/ui/Modal.vue';
import { type Room } from '@/types';

defineProps<{
    open: boolean;
    room: Room;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const roomUrl = window.location.href;

const shareRoom = () => {
    const message = `¡Te invito a dividir gastos en Cuanto Dolió! 💸\n\nÚnite acá: ${roomUrl}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
};

const copyLink = async () => {
    await navigator.clipboard.writeText(roomUrl);
};

const close = () => {
    emit('update:open', false);
};
</script>

<template>
    <Modal :open="open" @update:open="emit('update:open', $event)" title="¡Sala creada! 🎉">
        <div class="space-y-6 text-center">
            <div class="rounded-2xl bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 p-6">
                <p class="mb-2 text-sm text-slate-400">Código de sala</p>
                <p class="font-mono text-3xl font-bold tracking-widest text-white">{{ room.code }}</p>
            </div>

            <div class="space-y-3">
                <p class="text-slate-300"><strong>Paso 1:</strong> Compartí el link para que se unan todos los participantes</p>
                <p class="text-sm text-slate-500">Es importante que estén todos antes de cargar los gastos</p>
            </div>

            <div class="flex flex-col gap-3">
                <button
                    @click="shareRoom"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-6 py-4 font-bold text-white transition-all hover:bg-green-500 active:scale-[0.98]"
                >
                    📲 Compartir por WhatsApp
                </button>

                <button
                    @click="copyLink"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-6 py-3 font-medium text-white transition-all hover:bg-white/20"
                >
                    📋 Copiar link
                </button>
            </div>

            <button @click="close" class="text-sm text-slate-500 hover:text-white">Ya compartí, continuar →</button>
        </div>
    </Modal>
</template>
