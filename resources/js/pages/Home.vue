<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    nickname: '',
    room_name: '',
});

const submit = () => {
    form.post('/rooms');
};
</script>

<template>
    <Head title="Cuanto Dolió? - Dividí gastos fácil" />

    <div class="min-h-screen overflow-hidden bg-[#080b12] text-white">
        <!-- Background gradients -->
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute -top-1/4 -left-1/4 h-[600px] w-[600px] rounded-full bg-violet-600/20 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[500px] w-[500px] rounded-full bg-blue-600/15 blur-[100px]"></div>
            <div
                class="absolute top-1/2 left-1/2 h-[300px] w-[300px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-fuchsia-500/10 blur-[80px]"
            ></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 min-h-screen">
            <!-- Desktop: Split Screen -->
            <div class="hidden min-h-screen lg:flex">
                <!-- Left: Hero (centered) -->
                <div class="flex w-1/2 flex-col items-center justify-center px-8 xl:px-12">
                    <div class="text-center">
                        <!-- Logo -->
                        <img src="/images/logo.png" alt="Cuanto Dolió?" class="mx-auto mb-8 h-48 w-auto drop-shadow-2xl xl:h-56 2xl:h-64" />

                        <!-- Título -->
                        <h1 class="mb-3 text-4xl leading-tight font-bold xl:text-5xl">Dividí gastos sin dolor de cabeza.</h1>

                        <!-- Subtítulo con acento -->
                        <p class="text-2xl text-slate-400 xl:text-3xl">
                            <span class="bg-gradient-to-r from-fuchsia-400 to-cyan-400 bg-clip-text font-medium text-transparent italic">
                                (solo de billetera)
                            </span>
                        </p>

                        <!-- Features subtle -->
                        <div class="mt-8 flex justify-center gap-6 text-sm text-slate-500">
                            <span>⚡ Sin registro</span>
                            <span>🔒 Sin login</span>
                            <span>✨ Gratis</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Form Card (centered) -->
                <div class="flex w-1/2 items-center justify-center px-8 xl:px-12">
                    <div class="w-full max-w-md">
                        <!-- Glassmorphism Card -->
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                            <form @submit.prevent="submit" class="space-y-5">
                                <Input
                                    v-model="form.room_name"
                                    label="Nombre de la sala (opcional)"
                                    placeholder="Ej: Viaje a Bariloche"
                                    :error="form.errors.room_name"
                                />

                                <Input
                                    v-model="form.nickname"
                                    label="Tu nombre"
                                    placeholder="Ej: Juan"
                                    :error="form.errors.nickname"
                                    autocomplete="name"
                                    autofocus
                                />

                                <Button
                                    type="submit"
                                    :loading="form.processing"
                                    :disabled="!form.nickname.trim()"
                                    full-width
                                    size="lg"
                                    class="!bg-gradient-to-r !from-violet-600 !to-fuchsia-600 hover:!from-violet-500 hover:!to-fuchsia-500"
                                >
                                    Crear Sala
                                </Button>
                            </form>

                            <div class="mt-6 border-t border-white/10 pt-5 text-center">
                                <p class="text-sm text-slate-500">¿Tenés código? Pedile el link a quien creó la sala</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile: Single Column -->
            <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12 lg:hidden">
                <!-- Logo -->
                <img src="/images/logo.png" alt="Cuanto Dolió?" class="mb-6 h-32 w-auto drop-shadow-xl sm:h-40" />

                <!-- Title -->
                <h1 class="mb-2 text-center text-2xl leading-tight font-bold sm:text-3xl">Dividí gastos sin dolor de cabeza.</h1>

                <p class="mb-8 text-center text-lg">
                    <span class="bg-gradient-to-r from-fuchsia-400 to-cyan-400 bg-clip-text text-transparent italic"> (solo de billetera) </span>
                </p>

                <!-- Form Card -->
                <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl">
                    <form @submit.prevent="submit" class="space-y-4">
                        <Input
                            v-model="form.room_name"
                            label="Nombre de la sala (opcional)"
                            placeholder="Ej: Viaje a Bariloche"
                            :error="form.errors.room_name"
                        />

                        <Input
                            v-model="form.nickname"
                            label="Tu nombre"
                            placeholder="Ej: Juan"
                            :error="form.errors.nickname"
                            autocomplete="name"
                            autofocus
                        />

                        <Button
                            type="submit"
                            :loading="form.processing"
                            :disabled="!form.nickname.trim()"
                            full-width
                            size="lg"
                            class="!bg-gradient-to-r !from-violet-600 !to-fuchsia-600"
                        >
                            Crear Sala
                        </Button>
                    </form>

                    <div class="mt-5 border-t border-white/10 pt-4 text-center">
                        <p class="text-xs text-slate-500">¿Tenés código? Pedile el link a quien creó la sala</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
