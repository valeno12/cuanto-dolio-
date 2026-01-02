<script setup lang="ts">
import Card from '@/components/ui/Card.vue';
import type { RoomLockedProps } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<RoomLockedProps>();
</script>

<template>
    <Head :title="`Sala ${room.code} bloqueada`" />

    <div class="safe-top safe-bottom flex min-h-dvh flex-col items-center justify-center px-4 py-12">
        <Card class="animate-scale-in w-full max-w-sm text-center" variant="elevated">
            <!-- Lock icon -->
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-500/20">
                <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>

            <h1 class="mb-2 text-xl font-bold text-white">Sala bloqueada</h1>

            <p class="mb-4 text-slate-400">
                La sala <span class="font-mono text-primary-400">{{ room.code }}</span> fue cerrada por su creador.
            </p>

            <p class="text-sm text-slate-500">Si creés que esto es un error, contactá a quien te compartió el link.</p>
        </Card>
    </div>
</template>
