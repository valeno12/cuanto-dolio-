<script setup lang="ts">
import type { RoomLockedProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<RoomLockedProps>();
</script>

<template>
    <Head :title="`Sala ${room.code} bloqueada - Cuanto Dolió?`" />

    <div class="min-h-screen overflow-hidden bg-[#080b12] text-white">
        <!-- Background gradients -->
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute -top-1/4 -left-1/4 h-[500px] w-[500px] rounded-full bg-red-600/10 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[400px] w-[400px] rounded-full bg-slate-600/10 blur-[100px]"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex min-h-screen flex-col items-center justify-center px-6 py-12">
            <!-- Logo grayscale -->
            <img src="/images/logo.png" alt="Cuanto Dolió?" class="mb-8 h-24 w-auto opacity-50 drop-shadow-xl grayscale sm:h-32" />

            <!-- Lock Card -->
            <div class="w-full max-w-md rounded-3xl border border-white/10 bg-white/5 p-8 text-center backdrop-blur-xl">
                <!-- Lock icon -->
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-red-500/30 bg-red-500/20">
                    <span class="text-4xl">🔒</span>
                </div>

                <h1 class="mb-3 text-2xl font-bold">Sala Cerrada</h1>

                <p class="mb-2 text-slate-400">
                    La sala <span class="font-mono font-bold text-violet-400">{{ room.code }}</span> ya no acepta nuevos participantes.
                </p>

                <p class="text-sm text-slate-500">Si ya sos parte de esta sala, usá el link original que te compartieron.</p>
            </div>
        </div>
    </div>
</template>
