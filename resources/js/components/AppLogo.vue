<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-sm shadow-cyan-500/30 shrink-0"
    >
        <AppLogoIcon class="size-5 text-white" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm leading-none group-data-[collapsible=icon]:hidden">
        <span class="truncate font-bold tracking-tight text-foreground"
            >Anzar Table Water</span
        >
        <span class="truncate text-[10px] text-cyan-600 dark:text-cyan-400 font-semibold"
            >Pure & Refreshing</span
        >
    </div>
</template>

