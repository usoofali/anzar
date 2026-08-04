<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-lg bg-cyan-600 text-white shadow-sm shadow-cyan-600/30"
    >
        <AppLogoIcon class="size-5 text-white" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm leading-none">
        <span class="truncate font-bold tracking-tight text-foreground"
            >Anzar Table Water</span
        >
        <span class="truncate text-[10px] text-muted-foreground font-medium"
            >Pure & Refreshing</span
        >
    </div>
</template>
