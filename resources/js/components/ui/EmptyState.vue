<script setup lang="ts">
import type { Component } from 'vue';
import { PackageOpen } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{
    title: string;
    description: string;
    actionText?: string;
    icon?: Component;
}>();

const emit = defineEmits<{
    (e: 'action'): void;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center p-8 text-center rounded-xl border border-dashed border-border bg-card/50 my-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-muted/60 text-muted-foreground mb-4">
            <component :is="icon || PackageOpen" class="h-7 w-7" />
        </div>
        <h3 class="text-base font-semibold text-foreground">{{ title }}</h3>
        <p class="text-sm text-muted-foreground max-w-sm mt-1 mb-5 leading-relaxed">{{ description }}</p>
        <Button v-if="actionText" @click="emit('action')">
            {{ actionText }}
        </Button>
    </div>
</template>
