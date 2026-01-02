<script setup lang="ts">
interface Props {
    variant?: 'default' | 'elevated';
    padding?: 'none' | 'sm' | 'md' | 'lg';
    hover?: boolean;
}

withDefaults(defineProps<Props>(), {
    variant: 'default',
    padding: 'md',
    hover: false,
});

const paddingClasses = {
    none: '',
    sm: 'p-3',
    md: 'p-4',
    lg: 'p-6',
};
</script>

<template>
    <div
        :class="[
            'glass rounded-2xl',
            paddingClasses[padding],
            hover ? 'transition-transform duration-200 hover:scale-[1.02] cursor-pointer' : '',
            variant === 'elevated' ? 'shadow-2xl' : '',
        ]"
    >
        <slot />
    </div>
</template>
