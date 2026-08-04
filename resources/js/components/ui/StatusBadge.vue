<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: string;
    label?: string;
}>();

const config = computed(() => {
    const s = props.status.toLowerCase();
    switch (s) {
        case 'active':
        case 'settled':
        case 'good':
        case 'good standing':
            return {
                bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                dot: 'bg-emerald-500',
                text: props.label || (s === 'active' ? 'Active' : s === 'settled' ? 'Settled' : 'Good Standing'),
            };
        case 'closed':
            return {
                bg: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                dot: 'bg-blue-500',
                text: props.label || 'Closed',
            };
        case 'open':
        case 'outstanding':
        case 'outstanding debt':
            return {
                bg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                dot: 'bg-amber-500',
                text: props.label || (s === 'open' ? 'Open Debt' : 'Outstanding'),
            };
        case 'overdue':
        case 'inactive':
        case 'danger':
            return {
                bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                dot: 'bg-rose-500',
                text: props.label || (s === 'overdue' ? 'Overdue' : 'Inactive'),
            };
        default:
            return {
                bg: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                dot: 'bg-slate-500',
                text: props.label || props.status,
            };
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold tracking-wide transition-colors"
        :class="config.bg"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="config.dot" />
        {{ config.text }}
    </span>
</template>
