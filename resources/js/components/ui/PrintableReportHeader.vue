<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    title: string;
    generatedAt: string;
    generatedBy: string;
}>();
</script>

<template>
    <div class="mb-6 border-b pb-4 pt-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <AppLogoIcon class="h-6 w-6 fill-current text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">ANZAR TABLE WATER</h1>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Production & Distribution Management System</p>
                </div>
            </div>

            <div class="text-right">
                <div class="inline-block rounded-md bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                    OFFICIAL REPORT
                </div>
                <p class="text-xs text-muted-foreground mt-1">Generated: {{ generatedAt }}</p>
                <p class="text-xs text-muted-foreground">By: {{ generatedBy }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground tracking-tight">{{ title }}</h2>
        </div>
    </div>
</template>
