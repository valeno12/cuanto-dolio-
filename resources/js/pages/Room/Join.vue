<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import type { RoomJoinProps } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<RoomJoinProps>();

const form = useForm({
    nickname: '',
    payment_alias: '',
});

const submit = () => {
    form.post(`/${props.room.code}/join`);
};

const displayTitle = computed(() => props.room.name || `Sala ${props.room.code}`);
</script>

<template>
    <Head :title="`Unirse a ${displayTitle} - Cuanto Dolió?`" />

    <div class="min-h-screen overflow-hidden bg-[#080b12] text-white">
        <!-- Background gradients -->
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute -top-1/4 -left-1/4 h-[600px] w-[600px] rounded-full bg-violet-600/20 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[500px] w-[500px] rounded-full bg-blue-600/15 blur-[100px]"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 min-h-screen">
            <!-- Desktop: Split Screen -->
            <div class="hidden min-h-screen lg:flex">
                <!-- Left: Hero (centered) -->
                <div class="flex w-1/2 flex-col items-center justify-center px-8 xl:px-12">
                    <div class="text-center">
                        <!-- Logo -->
                        <img src="/images/logo.png" alt="Cuanto Dolió?" class="mx-auto mb-8 h-44 w-auto drop-shadow-2xl xl:h-52" />

                        <!-- Invitation text -->
                        <p class="mb-2 text-lg text-slate-400">Te invitaron a</p>
                        <h1 class="text-3xl leading-tight font-bold xl:text-4xl">
                            {{ displayTitle }}
                        </h1>

                        <!-- Room code badge -->
                        <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-violet-500/30 bg-violet-500/10 px-4 py-2">
                            <span class="font-mono text-lg font-bold tracking-widest text-violet-400">
                                {{ room.code }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Form Card (centered) -->
                <div class="flex w-1/2 items-center justify-center px-8 xl:px-12">
                    <div class="w-full max-w-md">
                        <!-- Glassmorphism Card -->
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                            <h2 class="mb-6 text-xl font-bold">Unite a la sala</h2>

                            <form @submit.prevent="submit" class="space-y-5">
                                <Input
                                    v-model="form.nickname"
                                    label="Tu nombre"
                                    placeholder="Ej: Juan"
                                    :error="form.errors.nickname"
                                    autocomplete="name"
                                    autofocus
                                />

                                <Input
                                    v-model="form.payment_alias"
                                    label="Alias de pago (opcional)"
                                    placeholder="CBU / Alias MercadoPago"
                                    :error="form.errors.payment_alias"
                                />

                                <Button
                                    type="submit"
                                    :loading="form.processing"
                                    :disabled="!form.nickname.trim()"
                                    full-width
                                    size="lg"
                                    class="!bg-gradient-to-r !from-violet-600 !to-fuchsia-600 hover:!from-violet-500 hover:!to-fuchsia-500"
                                >
                                    Unirme
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile: Single Column -->
            <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12 lg:hidden">
                <!-- Logo -->
                <img src="/images/logo.png" alt="Cuanto Dolió?" class="mb-4 h-28 w-auto drop-shadow-xl sm:h-32" />

                <!-- Invitation -->
                <p class="mb-1 text-sm text-slate-400">Te invitaron a</p>
                <h1 class="mb-2 text-center text-xl font-bold sm:text-2xl">
                    {{ displayTitle }}
                </h1>

                <!-- Code badge -->
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1.5">
                    <span class="font-mono text-sm font-bold tracking-widest text-violet-400">
                        {{ room.code }}
                    </span>
                </div>

                <!-- Form Card -->
                <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl">
                    <form @submit.prevent="submit" class="space-y-4">
                        <Input
                            v-model="form.nickname"
                            label="Tu nombre"
                            placeholder="Ej: Juan"
                            :error="form.errors.nickname"
                            autocomplete="name"
                            autofocus
                        />

                        <Input
                            v-model="form.payment_alias"
                            label="Alias de pago (opcional)"
                            placeholder="CBU / Alias MercadoPago"
                            :error="form.errors.payment_alias"
                        />

                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="!form.nickname.trim()"
                            full-width
                            size="lg"
                            class="!bg-gradient-to-r !from-violet-600 !to-fuchsia-600"
                        >
                            Unirme
                        </Button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
