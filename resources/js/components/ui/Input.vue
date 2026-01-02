<script setup lang="ts">
import { computed, useAttrs } from 'vue';

interface Props {
    modelValue?: string | number;
    label?: string;
    error?: string;
    type?: 'text' | 'email' | 'password' | 'number' | 'tel';
    icon?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    label: '',
    error: '',
    type: 'text',
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const attrs = useAttrs();

const inputId = computed(() => attrs.id as string || `input-${Math.random().toString(36).slice(2, 9)}`);

const hasValue = computed(() => {
    return props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined;
});

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', props.type === 'number' ? Number(target.value) : target.value);
};
</script>

<template>
    <div class="relative">
        <input
            :id="inputId"
            :type="type"
            :value="modelValue"
            @input="handleInput"
            :class="[
                'peer w-full bg-white/5 border rounded-xl px-4 pt-6 pb-2 text-white placeholder-transparent',
                'transition-all duration-200 focus-ring',
                error
                    ? 'border-red-500/50 focus:border-red-500'
                    : 'border-white/10 hover:border-white/20 focus:border-primary-500',
            ]"
            placeholder=" "
            v-bind="$attrs"
        />
        
        <!-- Floating label -->
        <label
            :for="inputId"
            :class="[
                'absolute left-4 transition-all duration-200 pointer-events-none',
                'peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-base',
                'peer-focus:top-2 peer-focus:text-xs peer-focus:text-primary-400',
                hasValue ? 'top-2 text-xs text-slate-400' : 'top-1/2 -translate-y-1/2 text-base text-slate-400',
                error ? 'text-red-400' : '',
            ]"
        >
            {{ label }}
        </label>
        
        <!-- Error message -->
        <p
            v-if="error"
            class="mt-1.5 text-sm text-red-400 animate-fade-in"
        >
            {{ error }}
        </p>
    </div>
</template>
