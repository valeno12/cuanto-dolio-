<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Input from '@/components/ui/Input.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    nickname: '',
});

const submit = () => {
    form.post('/rooms');
};
</script>

<template>
    <Head title="Dividí gastos fácil" />

    <div class="safe-top safe-bottom flex min-h-dvh flex-col items-center justify-center px-4 py-12">
        <!-- Hero section -->
        <div class="animate-fade-in mb-10 text-center">
            <!-- Logo / Icon -->
            <div class="relative mb-6 inline-block">
                <div
                    class="animate-pulse-glow flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-primary-500 to-secondary-500"
                >
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                </div>
            </div>

            <h1 class="mb-3 text-4xl font-bold tracking-tight sm:text-5xl">
                <span class="text-gradient">CuantoDolio</span>
            </h1>
            <p class="mx-auto max-w-md text-lg text-slate-400">Dividí gastos con amigos sin complicaciones. Sin registro, sin login.</p>
        </div>

        <!-- Create room form -->
        <Card class="animate-slide-up w-full max-w-sm" variant="elevated">
            <form @submit.prevent="submit" class="space-y-5">
                <Input v-model="form.nickname" label="Tu nombre o apodo" :error="form.errors.nickname" autocomplete="name" autofocus />

                <Button type="submit" :loading="form.processing" :disabled="!form.nickname.trim()" full-width size="lg"> Crear Sala </Button>
            </form>

            <div class="mt-6 border-t border-white/10 pt-5">
                <p class="text-center text-sm text-slate-400">¿Ya tenés un código de sala?</p>
                <p class="mt-1 text-center text-sm text-slate-500">Pedile el link a quien la creó</p>
            </div>
        </Card>

        <!-- Features -->
        <div class="animate-slide-up mt-16 grid w-full max-w-lg grid-cols-3 gap-6 delay-150">
            <div class="text-center">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-500/20">
                    <svg class="h-6 w-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <p class="text-xs text-slate-400">Rápido</p>
            </div>
            <div class="text-center">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary-500/20">
                    <svg class="h-6 w-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                </div>
                <p class="text-xs text-slate-400">Sin login</p>
            </div>
            <div class="text-center">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500/20">
                    <svg class="h-6 w-6 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                </div>
                <p class="text-xs text-slate-400">Grupal</p>
            </div>
        </div>
    </div>
</template>
