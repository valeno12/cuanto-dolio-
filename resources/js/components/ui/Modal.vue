<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';

interface Props {
    open: boolean;
    title?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    close: [];
}>();

const modalRef = ref<HTMLElement | null>(null);

const close = () => {
    emit('update:open', false);
    emit('close');
};

// Handle escape key
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.open) {
        close();
    }
};

// Lock body scroll when modal is open
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="close"
                />
                
                <!-- Modal content -->
                <Transition
                    enter-active-class="duration-300 ease-out"
                    enter-from-class="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
                    enter-to-class="translate-y-0 sm:scale-100 opacity-100"
                    leave-active-class="duration-200 ease-in"
                    leave-from-class="translate-y-0 sm:scale-100 opacity-100"
                    leave-to-class="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
                >
                    <div
                        v-if="open"
                        ref="modalRef"
                        class="relative w-full sm:max-w-md glass rounded-t-3xl sm:rounded-2xl safe-bottom max-h-[90vh] overflow-y-auto"
                        role="dialog"
                        aria-modal="true"
                    >
                        <!-- Drag indicator (mobile) -->
                        <div class="sm:hidden flex justify-center pt-3 pb-1">
                            <div class="w-10 h-1 bg-white/20 rounded-full" />
                        </div>
                        
                        <!-- Header -->
                        <div v-if="title" class="flex items-center justify-between px-5 py-4 border-b border-white/10">
                            <h2 class="text-lg font-semibold text-white">
                                {{ title }}
                            </h2>
                            <button
                                @click="close"
                                class="p-2 -mr-2 text-slate-400 hover:text-white transition-colors touch-target"
                                aria-label="Cerrar"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5">
                            <slot />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
