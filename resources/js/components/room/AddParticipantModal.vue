<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Modal from '@/components/ui/Modal.vue';
import type { Room } from '@/types';
import { useForm } from '@inertiajs/vue3';

interface Props {
    room: Room;
    open: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(`/${props.room.code}/participants`, {
        onSuccess: () => {
            form.reset();
            emit('update:open', false);
            emit('success');
        },
    });
};

const close = () => {
    emit('update:open', false);
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <Modal :open="open" title="Agregar participante" @close="close">
        <form @submit.prevent="submit" class="space-y-5">
            <p class="mb-4 text-sm text-slate-400">Agregá a alguien que no tiene celular o no puede unirse con el link.</p>

            <Input v-model="form.name" label="Nombre" :error="form.errors.name" autofocus />

            <div class="flex gap-3">
                <Button type="button" variant="ghost" @click="close" class="flex-1"> Cancelar </Button>
                <Button type="submit" :loading="form.processing" :disabled="!form.name.trim()" class="flex-1"> Agregar </Button>
            </div>
        </form>
    </Modal>
</template>
