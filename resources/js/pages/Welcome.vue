<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Cuanto Dolió? - Dividí gastos fácil" />

    <div class="min-h-screen overflow-hidden bg-[#080b12] text-white">
        <!-- Background gradients -->
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute -top-1/4 -left-1/4 h-[600px] w-[600px] rounded-full bg-violet-600/20 blur-[120px]"></div>
            <div class="absolute -right-1/4 -bottom-1/4 h-[500px] w-[500px] rounded-full bg-blue-600/15 blur-[100px]"></div>
            <div
                class="absolute top-1/2 left-1/2 h-[300px] w-[300px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-fuchsia-500/10 blur-[80px]"
            ></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 min-h-screen">
            <!-- Desktop: Split Screen -->
            <div class="hidden min-h-screen lg:flex">
                <!-- Left: Hero (centered) -->
                <div class="flex w-1/2 flex-col items-center justify-center px-8 xl:px-12">
                    <div class="text-center">
                        <!-- Logo -->
                        <img src="/images/logo.png" alt="Cuanto Dolió?" class="mx-auto mb-8 h-48 w-auto drop-shadow-2xl xl:h-56 2xl:h-64" />

                        <!-- Título -->
                        <h1 class="mb-3 text-4xl leading-tight font-bold xl:text-5xl">Dividí gastos sin dolor de cabeza.</h1>

                        <!-- Subtítulo con acento -->
                        <p class="text-2xl text-slate-400 xl:text-3xl">
                            <span class="bg-gradient-to-r from-fuchsia-400 to-cyan-400 bg-clip-text font-medium text-transparent italic">
                                (solo de billetera)
                            </span>
                        </p>

                        <!-- Features subtle -->
                        <div class="mt-8 flex justify-center gap-6 text-sm text-slate-500">
                            <span>⚡ Sin registro</span>
                            <span>🔒 Sin login</span>
                            <span>✨ Gratis</span>
                        </div>
                    </div>
                </div>

                <!-- Right: CTA (centered) -->
                <div class="flex w-1/2 items-center justify-center px-8 xl:px-12">
                    <div class="text-center">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-10 shadow-2xl backdrop-blur-xl">
                            <h2 class="mb-4 text-2xl font-bold">¿Listo para empezar?</h2>
                            <p class="mb-8 text-slate-400">Creá una sala en segundos y compartila con tu grupo.</p>

                            <Link
                                href="/home"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-12 py-4 text-lg font-bold text-white shadow-xl shadow-violet-500/25 transition-all hover:scale-105 hover:shadow-violet-500/40 active:scale-95"
                            >
                                <span>Crear Sala</span>
                                <svg
                                    class="h-5 w-5 transition-transform group-hover:translate-x-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile: Single Column -->
            <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12 lg:hidden">
                <!-- Logo -->
                <img src="/images/logo.png" alt="Cuanto Dolió?" class="mb-6 h-32 w-auto drop-shadow-xl sm:h-40" />

                <!-- Title -->
                <h1 class="mb-2 text-center text-2xl leading-tight font-bold sm:text-3xl">Dividí gastos sin dolor de cabeza.</h1>

                <p class="mb-8 text-center text-lg">
                    <span class="bg-gradient-to-r from-fuchsia-400 to-cyan-400 bg-clip-text text-transparent italic"> (solo de billetera) </span>
                </p>

                <!-- CTA -->
                <Link
                    href="/home"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-10 py-4 text-lg font-bold text-white shadow-xl shadow-violet-500/25 transition-all hover:scale-105 active:scale-95"
                >
                    <span>Crear Sala</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>

                <!-- Features -->
                <div class="mt-12 flex gap-6 text-sm text-slate-500">
                    <span>⚡ Rápido</span>
                    <span>🔓 Sin registro</span>
                    <span>👥 Tiempo real</span>
                </div>
            </div>
        </div>
    </div>
</template>
