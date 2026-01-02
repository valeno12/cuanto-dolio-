<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import type { RoomJoinProps } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<RoomJoinProps>();

const form = useForm({
    nickname: '',
});

const submit = () => {
    form.post(`/${props.room.code}/join`);
};
</script>

<template>
    <Head :title="`Unirse a ${room.code}`" />

    <div class="safe-top safe-bottom flex min-h-dvh flex-col items-center justify-center px-4 py-12">
        <!-- Room code badge -->
        <div class="animate-fade-in mb-8">
            <div class="rounded-full border border-primary-500/30 bg-primary-500/20 px-4 py-2">
                <span class="font-mono font-bold tracking-widest text-primary-400">
                    {{ room.code }}
                </span>
            </div>
        </div>

        <!-- Title -->
        <div class="animate-slide-up mb-8 text-center">
            <h1 class="mb-2 text-2xl font-bold text-white sm:text-3xl">Unite a la sala</h1>
            <p class="text-slate-400">Ingresá tu nombre para empezar</p>
        </div>

        <!-- Join form -->
        <Card class="animate-slide-up w-full max-w-sm delay-75" variant="elevated">
            <form @submit.prevent="submit" class="space-y-5">
                <Input v-model="form.nickname" label="Tu nombre o apodo" :error="form.errors.nickname" autocomplete="name" autofocus />

                <Button type="submit" :loading="form.processing" :disabled="!form.nickname.trim()" full-width size="lg"> Unirme </Button>
            </form>
        </Card>
    </div>
</template>
