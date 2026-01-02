<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    modelValue: 'expenses' | 'settlement' | 'profile';
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: 'expenses' | 'settlement' | 'profile'): void;
}>();

const tabs = [
    { id: 'expenses', label: 'Gastos', icon: '🧾' },
    { id: 'settlement', label: 'Pagos', icon: '💸' },
    { id: 'profile', label: 'Perfil', icon: '👤' },
] as const;
</script>

<template>
    <nav class="fixed bottom-0 left-0 z-40 w-full border-t border-white/5 bg-slate-900/95 pb-safe backdrop-blur-lg">
        <div class="mx-auto flex max-w-md items-center justify-around px-2 py-3">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="emit('update:modelValue', tab.id)"
                class="group relative flex flex-1 flex-col items-center gap-1 p-2 transition-colors"
                :class="modelValue === tab.id ? 'text-primary-400' : 'text-slate-500 hover:text-slate-400'"
            >
                <!-- Active Indicator -->
                <div 
                    v-if="modelValue === tab.id"
                    class="absolute -top-3 h-1 w-12 rounded-full bg-primary-500 shadow-[0_0_10px_2px_rgba(var(--primary-500),0.5)]"
                ></div>

                <span class="text-2xl transition-transform duration-300 group-active:scale-90">
                    {{ tab.icon }}
                </span>
                <span class="text-[10px] font-medium">{{ tab.label }}</span>
            </button>
        </div>
    </nav>
</template>
