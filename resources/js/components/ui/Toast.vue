<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';

interface Toast {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
    duration: number;
}

const toasts = ref<Toast[]>([]);

// Listen for custom toast events
const addToast = (event: CustomEvent<{ message: string; type?: 'success' | 'error' | 'info'; duration?: number }>) => {
    const id = Date.now();
    const toast: Toast = {
        id,
        message: event.detail.message,
        type: event.detail.type || 'info',
        duration: event.detail.duration || 3000,
    };
    
    toasts.value.push(toast);
    
    setTimeout(() => {
        removeToast(id);
    }, toast.duration);
};

const removeToast = (id: number) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

onMounted(() => {
    window.addEventListener('show-toast', addToast as EventListener);
});

const getToastClasses = (type: Toast['type']) => {
    switch (type) {
        case 'success':
            return 'bg-green-500/90 text-white';
        case 'error':
            return 'bg-red-500/90 text-white';
        default:
            return 'bg-slate-800/90 text-white';
    }
};

const getIcon = (type: Toast['type']) => {
    switch (type) {
        case 'success':
            return '✓';
        case 'error':
            return '✕';
        default:
            return 'ℹ';
    }
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto flex items-center gap-3 rounded-xl px-4 py-3 shadow-xl backdrop-blur-sm min-w-[200px] max-w-[90vw]',
                        getToastClasses(toast.type)
                    ]"
                >
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-sm font-bold">
                        {{ getIcon(toast.type) }}
                    </span>
                    <p class="text-sm font-medium">{{ toast.message }}</p>
                    <button 
                        @click="removeToast(toast.id)"
                        class="ml-auto text-white/60 hover:text-white"
                    >
                        ×
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
