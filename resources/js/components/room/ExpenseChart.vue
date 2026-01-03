<script setup lang="ts">
import { getCategory } from '@/constants/categories';
import type { Expense } from '@/types';
import { computed } from 'vue';

interface Props {
    expenses: Expense[];
}

const props = defineProps<Props>();

// Calculate totals by category
const categoryTotals = computed(() => {
    const totals: Record<string, number> = {};

    for (const expense of props.expenses) {
        const category = expense.category || 'other';
        totals[category] = (totals[category] || 0) + parseFloat(expense.amount);
    }

    // Sort by amount descending
    return Object.entries(totals)
        .map(([categoryId, amount]) => ({
            ...getCategory(categoryId),
            categoryId,
            amount,
        }))
        .sort((a, b) => b.amount - a.amount);
});

const total = computed(() => categoryTotals.value.reduce((sum, cat) => sum + cat.amount, 0));

// Create SVG donut chart paths
const chartSegments = computed(() => {
    if (total.value === 0) return [];

    const segments: { path: string; color: string; label: string; amount: number; percentage: number }[] = [];
    let currentAngle = -90; // Start from top

    const categoryColors: Record<string, string> = {
        food: '#f97316', // orange-500
        drinks: '#f59e0b', // amber-500
        market: '#10b981', // emerald-500
        transport: '#3b82f6', // blue-500
        entertainment: '#a855f7', // purple-500
        lodging: '#6366f1', // indigo-500
        gifts: '#ec4899', // pink-500
        other: '#64748b', // slate-500
    };

    for (const cat of categoryTotals.value) {
        const percentage = (cat.amount / total.value) * 100;
        const angle = (percentage / 100) * 360;

        const startAngle = currentAngle;
        const endAngle = currentAngle + angle;

        // Calculate arc path
        const startRad = (startAngle * Math.PI) / 180;
        const endRad = (endAngle * Math.PI) / 180;

        const r = 42; // radius
        const cx = 50,
            cy = 50; // center

        const x1 = cx + r * Math.cos(startRad);
        const y1 = cy + r * Math.sin(startRad);
        const x2 = cx + r * Math.cos(endRad);
        const y2 = cy + r * Math.sin(endRad);

        const largeArc = angle > 180 ? 1 : 0;

        // Create path for the donut segment
        const innerR = 28;
        const ix1 = cx + innerR * Math.cos(startRad);
        const iy1 = cy + innerR * Math.sin(startRad);
        const ix2 = cx + innerR * Math.cos(endRad);
        const iy2 = cy + innerR * Math.sin(endRad);

        const path = `
            M ${x1} ${y1}
            A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2}
            L ${ix2} ${iy2}
            A ${innerR} ${innerR} 0 ${largeArc} 0 ${ix1} ${iy1}
            Z
        `;

        segments.push({
            path,
            color: categoryColors[cat.categoryId] || categoryColors.other,
            label: cat.label,
            amount: cat.amount,
            percentage,
        });

        currentAngle = endAngle;
    }

    return segments;
});

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <div class="rounded-2xl border border-white/5 bg-slate-800/50 p-5">
        <h3 class="mb-4 text-sm font-bold text-slate-300">Gastos por categoría</h3>

        <div v-if="expenses.length === 0" class="flex flex-col items-center py-8 text-slate-500">
            <span class="mb-2 text-3xl">📊</span>
            <p class="text-sm">Sin gastos aún</p>
        </div>

        <div v-else class="flex items-start gap-6">
            <!-- Donut Chart -->
            <div class="relative flex-shrink-0">
                <svg viewBox="0 0 100 100" class="h-32 w-32">
                    <path
                        v-for="(segment, index) in chartSegments"
                        :key="index"
                        :d="segment.path"
                        :fill="segment.color"
                        class="transition-all duration-300 hover:opacity-80"
                    />
                    <!-- Center text -->
                    <text x="50" y="47" text-anchor="middle" class="fill-slate-400 text-[6px] font-medium">TOTAL</text>
                    <text x="50" y="56" text-anchor="middle" class="fill-white text-[8px] font-bold">
                        {{ formatCurrency(total) }}
                    </text>
                </svg>
            </div>

            <!-- Legend -->
            <div class="flex-1 space-y-2">
                <div v-for="cat in categoryTotals" :key="cat.categoryId" class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-base">{{ cat.icon }}</span>
                        <span class="text-slate-300">{{ cat.label }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-500">{{ Math.round((cat.amount / total) * 100) }}%</span>
                        <span class="font-medium text-white tabular-nums">{{ formatCurrency(cat.amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
