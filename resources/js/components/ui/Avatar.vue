<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    name: string;
    size?: 'sm' | 'md' | 'lg';
    showName?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    showName: false,
});

// Get initials from name
const initials = computed(() => {
    return props.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

// Generate consistent color based on name
const backgroundColor = computed(() => {
    const colors = [
        'bg-violet-500',
        'bg-emerald-500',
        'bg-amber-500',
        'bg-rose-500',
        'bg-cyan-500',
        'bg-fuchsia-500',
        'bg-lime-500',
        'bg-orange-500',
    ];
    
    // Simple hash based on name
    let hash = 0;
    for (let i = 0; i < props.name.length; i++) {
        hash = props.name.charCodeAt(i) + ((hash << 5) - hash);
    }
    
    return colors[Math.abs(hash) % colors.length];
});

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-14 w-14 text-lg',
};
</script>

<template>
    <div class="flex items-center gap-2">
        <div
            :class="[
                'rounded-full flex items-center justify-center font-semibold text-white shrink-0',
                backgroundColor,
                sizeClasses[size],
            ]"
        >
            {{ initials }}
        </div>
        <span v-if="showName" class="text-white font-medium truncate">
            {{ name }}
        </span>
    </div>
</template>
