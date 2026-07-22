<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const currentStep = ref(0);

const steps = [
    {
        title: '¡Bienvenido a Cuanto Dolió! 👋',
        subtitle: 'La forma más fácil de dividir gastos grupales',
        content: `
            <p class="mb-4">Ya sea un viaje, una juntada, o gastos compartidos del mes, <strong>Cuanto Dolió</strong> te ayuda a dividir gastos sin complicaciones.</p>
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="text-center p-4 rounded-xl bg-violet-500/10 border border-violet-500/20">
                    <span class="text-3xl mb-2 block">⚡</span>
                    <p class="text-sm text-slate-300">Sin registro</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-fuchsia-500/10 border border-fuchsia-500/20">
                    <span class="text-3xl mb-2 block">🔒</span>
                    <p class="text-sm text-slate-300">Sin login</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-cyan-500/10 border border-cyan-500/20">
                    <span class="text-3xl mb-2 block">✨</span>
                    <p class="text-sm text-slate-300">100% Gratis</p>
                </div>
            </div>
        `,
        icon: '🚀',
    },
    {
        title: 'Paso 1: Creá una sala',
        subtitle: 'El punto de encuentro para tu grupo',
        content: `
            <p class="mb-4">Una <strong>sala</strong> es donde vos y tu grupo van a cargar todos los gastos. Cada sala tiene un código único que podés compartir.</p>
            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 mt-4">
                <p class="text-sm text-slate-400 mb-2">💡 Consejo</p>
                <p class="text-sm">Ponele un nombre descriptivo como "Viaje a Bariloche" o "Asado Trabajo" para identificarla fácil.</p>
            </div>
            <div class="mt-6 flex items-center justify-center gap-6">
                <div class="text-center">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-3xl mb-2">📝</div>
                    <p class="text-xs text-slate-400">Nombre de sala + Tu nombre</p>
                </div>
                <span class="text-3xl text-slate-600">→</span>
                <div class="text-center">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-3xl mb-2">✅</div>
                    <p class="text-xs text-slate-400">¡Sala creada!</p>
                </div>
            </div>
        `,
        icon: '🏠',
    },
    {
        title: 'Paso 2: Invitá a tu grupo',
        subtitle: 'Compartí el link para que se unan todos',
        content: `
            <p class="mb-4">Una vez creada la sala, compartí el link con todos los que van a participar. Pueden unirse con un solo click.</p>
            <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl p-4 border border-green-500/20 mt-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl">📲</span>
                    <p class="font-medium">Compartir por WhatsApp</p>
                </div>
                <p class="text-sm text-slate-400">La forma más rápida de invitar a todos</p>
            </div>
            <div class="mt-6 p-4 bg-slate-800/30 rounded-xl text-center">
                <p class="text-xs text-slate-500 mb-1">Ejemplo de link</p>
                <code class="text-lg text-violet-400">cuantodolio.app/ABC123</code>
            </div>
        `,
        icon: '👥',
    },
    {
        title: 'Usuarios virtuales 👻',
        subtitle: 'Para quienes no tienen la app',
        content: `
            <p class="mb-4">¿Alguien del grupo no puede unirse a la sala? El <strong>administrador</strong> puede agregar <strong>usuarios virtuales</strong>.</p>
            <div class="space-y-3 mt-4">
                <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-xl border border-slate-700">
                    <div class="w-10 h-10 rounded-full bg-violet-500 flex items-center justify-center text-sm font-bold">JU</div>
                    <span class="font-medium">Juan</span>
                    <span class="ml-auto text-xs bg-violet-500/20 text-violet-400 px-2 py-1 rounded-full">Admin</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-xl border border-dashed border-slate-600">
                    <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-lg">👻</div>
                    <span class="text-slate-400">Abuelo (virtual)</span>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-4">Los usuarios virtuales participan de los gastos pero son manejados por el admin.</p>
        `,
        icon: '👻',
    },
    {
        title: 'Paso 3: Carguen los gastos',
        subtitle: 'Cada uno carga lo que pagó',
        content: `
            <p class="mb-4">Cargá <strong>quién pagó</strong>, el monto, la descripción y <strong>entre quiénes se divide</strong>.</p>
            <div class="space-y-3 mt-4">
                <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-xl border border-slate-700">
                    <span class="text-2xl">🛒</span>
                    <div class="flex-1">
                        <p class="font-medium">Supermercado</p>
                        <p class="text-xs text-slate-400">Juan pagó $15.000</p>
                    </div>
                    <span class="text-sm text-slate-400">÷ 4</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-xl border border-slate-700">
                    <span class="text-2xl">⛽</span>
                    <div class="flex-1">
                        <p class="font-medium">Nafta</p>
                        <p class="text-xs text-slate-400">Pedro pagó $20.000</p>
                    </div>
                    <span class="text-sm text-violet-400 font-bold">÷ 2 👈</span>
                </div>
            </div>
            <div class="mt-4 p-3 bg-violet-500/10 rounded-xl border border-violet-500/20">
                <p class="text-sm text-violet-300">💡 <strong>Podés elegir entre quiénes dividir.</strong> No todos tienen que participar de todos los gastos.</p>
            </div>
        `,
        icon: '💰',
    },
    {
        title: 'División personalizada 🎯',
        subtitle: 'Dividí solo entre quienes corresponde',
        content: `
            <p class="mb-4">No todos los gastos son de todos. <strong>Podés elegir exactamente quiénes participan</strong> de cada gasto.</p>
            
            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 mb-4">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-2xl">🍹</span>
                    <div>
                        <p class="font-medium">Fernet + Coca</p>
                        <p class="text-xs text-slate-400">María pagó $8.000</p>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mb-3">¿Entre quiénes se divide?</p>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex items-center gap-2 p-2 bg-green-500/20 rounded-lg border border-green-500/30">
                        <span class="text-green-400">✓</span>
                        <span class="text-sm">Juan</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-green-500/20 rounded-lg border border-green-500/30">
                        <span class="text-green-400">✓</span>
                        <span class="text-sm">María</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-slate-700/50 rounded-lg border border-slate-600">
                        <span class="text-slate-500">✗</span>
                        <span class="text-sm text-slate-400">Pedro</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-green-500/20 rounded-lg border border-green-500/30">
                        <span class="text-green-400">✓</span>
                        <span class="text-sm">Ana</span>
                    </div>
                </div>
            </div>
            
            <div class="p-3 bg-fuchsia-500/10 rounded-xl border border-fuchsia-500/20">
                <p class="text-sm text-fuchsia-300">🎯 Pedro no tomó fernet, así que <strong>no participa</strong> de ese gasto. Solo pagan Juan, María y Ana.</p>
            </div>
        `,
        icon: '🎯',
    },
    {
        title: 'Paso 4: Cerrá la sala',
        subtitle: 'Cuando terminen de cargar gastos',
        content: `
            <p class="mb-4">Una vez que terminaron de cargar todos los gastos, el <strong>administrador</strong> cierra la sala para calcular las deudas automáticamente.</p>
            <div class="flex items-center justify-center my-8">
                <div class="flex items-center gap-6">
                    <div class="w-24 h-24 rounded-2xl bg-slate-800 flex items-center justify-center border border-slate-700">
                        <span class="text-4xl">📝</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-2xl mb-1">🔒</span>
                        <span class="text-xs text-slate-400">Cerrar</span>
                    </div>
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-secondary-500 to-secondary-600 flex items-center justify-center shadow-lg shadow-secondary-500/20">
                        <span class="text-4xl">💸</span>
                    </div>
                </div>
            </div>
            <div class="bg-amber-500/10 rounded-xl p-4 border border-amber-500/20">
                <p class="text-sm text-amber-400">⚠️ Una vez cerrada, no se pueden agregar más gastos</p>
            </div>
        `,
        icon: '🔒',
    },
    {
        title: '✨ El Simplificador de Transferencias',
        subtitle: 'La magia de Cuanto Dolió',
        content: `
            <p class="mb-4">En vez de que todos se deban plata entre sí, <strong>minimizamos las transferencias</strong> al mínimo necesario.</p>
            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="p-4 bg-red-500/10 rounded-xl border border-red-500/20">
                    <p class="text-xs text-red-400 mb-2 font-bold">❌ SIN SIMPLIFICAR</p>
                    <div class="space-y-1 text-sm text-slate-400">
                        <p>Facu → Leo: $2.000</p>
                        <p>Leo → Sofi: $2.000</p>
                        <p>Facu → Nico: $1.500</p>
                        <p>Nico → Sofi: $1.500</p>
                        <p>Facu → Sofi: $1.000</p>
                    </div>
                    <p class="mt-3 text-red-400 font-bold text-lg">5 transferencias 😵</p>
                </div>
                <div class="p-4 bg-green-500/10 rounded-xl border border-green-500/20">
                    <p class="text-xs text-green-400 mb-2 font-bold">✅ SIMPLIFICADO</p>
                    <div class="space-y-1 text-sm text-slate-400">
                        <p>Facu → Sofi: $4.500</p>
                        <p class="opacity-0">-</p>
                        <p class="opacity-0">-</p>
                        <p class="opacity-0">-</p>
                        <p class="opacity-0">-</p>
                    </div>
                    <p class="mt-3 text-green-400 font-bold text-lg">¡Solo 1! 🎉</p>
                </div>
            </div>
            <p class="mt-4 text-center text-sm text-slate-400">El sistema calcula automáticamente quién le paga a quién</p>
        `,
        icon: '🧮',
    },
    {
        title: 'Paso 5: ¡A pagar!',
        subtitle: 'Cada uno ve exactamente qué debe',
        content: `
            <p class="mb-4">Después de cerrar la sala, cada participante ve su <strong>balance personal</strong>: cuánto debe y a quién, o cuánto le deben.</p>
            <div class="p-4 bg-red-500/10 rounded-xl border border-red-500/20 mt-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm">Tenés que pagar</p>
                    <p class="text-2xl font-bold text-red-400">$6.500</p>
                </div>
                <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-violet-500 flex items-center justify-center text-sm font-bold">PE</div>
                    <div class="flex-1">
                        <p class="font-medium">Pedro</p>
                        <p class="text-xs text-slate-400">mercadopago.alias</p>
                    </div>
                    <button class="px-4 py-2 bg-primary-500 rounded-lg text-sm font-bold">Pagar</button>
                </div>
            </div>
            <div class="mt-4 bg-slate-800/30 rounded-xl p-4">
                <p class="text-sm text-slate-400">💡 Podés copiar el alias de pago con un click y después marcar el pago como realizado.</p>
            </div>
        `,
        icon: '💸',
    },
    {
        title: '¡Listo! 🎉',
        subtitle: 'Así de fácil es usar Cuanto Dolió',
        content: `
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 mb-4">
                    <span class="text-5xl">🥳</span>
                </div>
                <p class="text-lg font-medium">¡Ya sabés todo lo que necesitás!</p>
            </div>
            <div class="space-y-3">
                <div class="flex items-center gap-4 p-4 bg-slate-800/50 rounded-xl">
                    <span class="text-2xl">1️⃣</span>
                    <p>Creá la sala</p>
                </div>
                <div class="flex items-center gap-4 p-4 bg-slate-800/50 rounded-xl">
                    <span class="text-2xl">2️⃣</span>
                    <p>Invitá al grupo</p>
                </div>
                <div class="flex items-center gap-4 p-4 bg-slate-800/50 rounded-xl">
                    <span class="text-2xl">3️⃣</span>
                    <p>Carguen gastos</p>
                </div>
                <div class="flex items-center gap-4 p-4 bg-slate-800/50 rounded-xl">
                    <span class="text-2xl">4️⃣</span>
                    <p>Cerrá y liquidá</p>
                </div>
            </div>
        `,
        icon: '✅',
    },
];

const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const goBack = () => {
    // Try to go back in history, fallback to home
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
};
</script>

<template>
    <Head title="¿Cómo funciona? - Cuanto Dolió?" />

    <div class="min-h-dvh bg-[#080b12] text-white">
        <!-- Background gradients -->
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute -top-1/4 -left-1/4 h-[600px] w-[600px] rounded-full bg-violet-600/20 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[500px] w-[500px] rounded-full bg-blue-600/15 blur-[100px]"></div>
        </div>

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-white/5 bg-[#080b12]/95 backdrop-blur-md px-4 py-4">
            <div class="mx-auto max-w-2xl flex items-center justify-between">
                <button @click="goBack" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="text-sm">Volver</span>
                </button>
                <img src="/images/logo.png" alt="Cuanto Dolió?" class="h-10 w-auto" />
                <div class="text-sm text-slate-500">{{ currentStep + 1 }}/{{ steps.length }}</div>
            </div>
        </header>

        <div class="relative z-10 mx-auto max-w-2xl px-4 py-6 pb-28">
            <!-- Progress bar -->
            <div class="mb-6">
                <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 transition-all duration-500 ease-out"
                        :style="{ width: `${((currentStep + 1) / steps.length) * 100}%` }"
                    ></div>
                </div>
            </div>

            <!-- Step content -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-x-4"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 -translate-x-4"
                mode="out-in"
            >
                <div :key="currentStep" class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-xl">
                    <!-- Step icon -->
                    <div class="mb-6 flex justify-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 text-5xl ring-1 ring-white/10">
                            {{ steps[currentStep].icon }}
                        </div>
                    </div>

                    <!-- Step title -->
                    <h1 class="mb-2 text-center text-2xl font-bold">{{ steps[currentStep].title }}</h1>
                    <p class="mb-6 text-center text-slate-400">{{ steps[currentStep].subtitle }}</p>

                    <!-- Step content -->
                    <div class="text-slate-300" v-html="steps[currentStep].content"></div>
                </div>
            </Transition>

            <!-- Step dots -->
            <div class="mt-6 flex justify-center gap-2">
                <button
                    v-for="(_, index) in steps"
                    :key="index"
                    @click="currentStep = index"
                    :class="[
                        'h-2 rounded-full transition-all',
                        currentStep === index ? 'w-6 bg-violet-500' : 'w-2 bg-slate-700 hover:bg-slate-600'
                    ]"
                ></button>
            </div>
        </div>

        <!-- Fixed Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-white/10 bg-[#080b12]/95 backdrop-blur-md px-4 py-4">
            <div class="mx-auto max-w-2xl flex gap-3">
                <button
                    v-if="currentStep > 0"
                    @click="prevStep"
                    class="flex-1 rounded-xl border border-slate-700 bg-slate-800/50 py-4 font-medium text-white transition-all hover:bg-slate-800 active:scale-[0.98]"
                >
                    ← Anterior
                </button>
                <button
                    v-if="currentStep < steps.length - 1"
                    @click="nextStep"
                    class="flex-1 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 py-4 font-bold text-white shadow-lg shadow-violet-500/25 transition-all hover:scale-[1.02] active:scale-[0.98]"
                >
                    Siguiente →
                </button>
                <button
                    v-if="currentStep === steps.length - 1"
                    @click="goBack"
                    class="flex flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 py-4 font-bold text-white shadow-lg shadow-violet-500/25 transition-all hover:scale-[1.02] active:scale-[0.98]"
                >
                    ¡Empezar! 🚀
                </button>
            </div>
        </div>
    </div>
</template>
