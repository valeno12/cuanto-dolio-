<script setup lang="ts">
import Avatar from '@/components/ui/Avatar.vue';
import type { Participant } from '@/types';

interface Props {
    participants: Participant[];
    currentParticipantId?: string;
    isAdmin?: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    addParticipant: [];
}>();

const getRoleBadge = (role: Participant['role']) => {
    switch (role) {
        case 'admin':
            return { text: 'Admin', class: 'bg-primary-500/20 text-primary-400' };
        case 'virtual':
            return { text: 'Virtual', class: 'bg-slate-500/20 text-slate-400' };
        default:
            return null;
    }
};
</script>

<template>
    <div class="no-scrollbar -mx-4 flex gap-3 overflow-x-auto px-4 pb-2">
        <div
            v-for="participant in participants"
            :key="participant.id"
            :class="[
                'flex shrink-0 flex-col items-center gap-1 rounded-xl p-2 transition-colors',
                participant.id === currentParticipantId ? 'bg-primary-500/10' : '',
            ]"
        >
            <div class="relative">
                <Avatar :name="participant.name" size="lg" />

                <!-- Current user indicator -->
                <div
                    v-if="participant.id === currentParticipantId"
                    class="absolute -right-0.5 -bottom-0.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-slate-900 bg-secondary-500"
                >
                    <svg class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
            </div>

            <span class="max-w-[60px] truncate text-xs font-medium text-slate-300">
                {{ participant.name }}
            </span>

            <!-- Role badge -->
            <span
                v-if="getRoleBadge(participant.role)"
                :class="['rounded-full px-1.5 py-0.5 text-[10px] font-medium', getRoleBadge(participant.role)?.class]"
            >
                {{ getRoleBadge(participant.role)?.text }}
            </span>
        </div>

        <!-- Add participant button (admin only) -->
        <button
            v-if="isAdmin"
            @click="emit('addParticipant')"
            class="touch-target flex shrink-0 flex-col items-center justify-center gap-1 rounded-xl p-2 transition-colors hover:bg-white/5"
        >
            <div
                class="flex h-14 w-14 items-center justify-center rounded-full border-2 border-dashed border-slate-600 text-slate-500 transition-colors hover:border-primary-500 hover:text-primary-400"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <span class="text-xs text-slate-500">Agregar</span>
        </button>
    </div>
</template>
