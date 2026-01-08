<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface MyRoom {
    id: string;
    code: string;
    name: string | null;
    is_locked: boolean;
    participant_count: number;
    expense_count: number;
    my_name: string;
    my_role: string;
}

const form = useForm({
    nickname: '',
    room_name: '',
});

const myRooms = ref<MyRoom[]>([]);
const loadingRooms = ref(true);

const submit = () => {
    form.post('/rooms');
};

const fetchMyRooms = async () => {
    try {
        const response = await fetch('/my-rooms');
        const data = await response.json();
        myRooms.value = data.rooms;
    } catch {
        // Silently fail - rooms section just won't show
    } finally {
        loadingRooms.value = false;
    }
};

const goToRoom = (code: string) => {
    router.visit(`/${code}`);
};

onMounted(() => {
    fetchMyRooms();
});
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

                <!-- Right: Form Card + My Rooms -->
                <div class="flex w-1/2 flex-col items-center justify-center gap-8 px-8 py-12 xl:px-12">
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

                            <div class="mt-6 border-t border-white/10 pt-5 text-center space-y-3">
                                <p class="text-sm text-slate-500">¿Tenés código? Pedile el link a quien creó la sala</p>
                                <Link href="/como-funciona" class="inline-flex items-center gap-2 text-sm text-violet-400 hover:text-violet-300 transition-colors">
                                    <span>❓</span>
                                    <span>¿Cómo funciona?</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- My Rooms Section (Desktop) -->
                    <div v-if="myRooms.length > 0" class="w-full max-w-md">
                        <h2 class="mb-3 text-sm font-bold tracking-widest text-slate-400 uppercase">Mis Salas</h2>
                        <div class="space-y-2">
                            <button
                                v-for="room in myRooms"
                                :key="room.id"
                                @click="goToRoom(room.code)"
                                class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 text-left transition-all hover:border-violet-500/30 hover:bg-white/10"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-white">{{ room.name || `Sala ${room.code}` }}</p>
                                    <p class="text-xs text-slate-400">
                                        👤 {{ room.my_name }} · 👥 {{ room.participant_count }} · 📝 {{ room.expense_count }} gastos
                                    </p>
                                </div>
                                <div class="ml-3 flex items-center gap-2">
                                    <span v-if="room.is_locked" class="rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-400">
                                        Cerrada
                                    </span>
                                    <span v-else class="rounded-full bg-violet-500/10 px-2 py-0.5 text-xs font-medium text-violet-400">
                                        Abierta
                                    </span>
                                    <span class="text-slate-500">→</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile: Single Column -->
            <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12 lg:hidden">
                <!-- Logo -->
                <img src="/images/logo.png" alt="Cuanto Dolió?" class="mb-6 h-40 w-auto drop-shadow-xl sm:h-48" />

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

                    <div class="mt-5 border-t border-white/10 pt-4 text-center space-y-2">
                        <p class="text-xs text-slate-500">¿Tenés código? Pedile el link a quien creó la sala</p>
                        <Link href="/como-funciona" class="inline-flex items-center gap-2 text-xs text-violet-400">
                            <span>❓</span>
                            <span>¿Cómo funciona?</span>
                        </Link>
                    </div>
                </div>

                <!-- My Rooms Section (Mobile) -->
                <div v-if="myRooms.length > 0" class="mt-8 w-full max-w-sm">
                    <h2 class="mb-3 text-center text-sm font-bold tracking-widest text-slate-400 uppercase">Mis Salas</h2>
                    <div class="space-y-2">
                        <button
                            v-for="room in myRooms"
                            :key="room.id"
                            @click="goToRoom(room.code)"
                            class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 text-left transition-all active:scale-[0.98]"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold text-white">{{ room.name || `Sala ${room.code}` }}</p>
                                <p class="text-xs text-slate-400">👤 {{ room.my_name }} · 👥 {{ room.participant_count }}</p>
                            </div>
                            <div class="ml-3 flex items-center gap-2">
                                <span v-if="room.is_locked" class="rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-400">
                                    Cerrada
                                </span>
                                <span v-else class="rounded-full bg-violet-500/10 px-2 py-0.5 text-xs font-medium text-violet-400"> Abierta </span>
                                <span class="text-slate-500">→</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
