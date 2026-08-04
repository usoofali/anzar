<script setup lang="ts">
import { AlertTriangle, Loader2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        confirmText?: string;
        cancelText?: string;
        variant?: 'destructive' | 'warning' | 'default';
        loading?: boolean;
    }>(),
    {
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        variant: 'destructive',
        loading: false,
    }
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
}>();

const handleClose = () => {
    if (!props.loading) {
        emit('update:open', false);
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex flex-row items-start gap-4">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    :class="
                        variant === 'destructive'
                            ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400'
                            : variant === 'warning'
                              ? 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'
                              : 'bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400'
                    "
                >
                    <AlertTriangle class="h-5 w-5" />
                </div>
                <div class="space-y-1">
                    <DialogTitle class="text-lg font-semibold">{{ title }}</DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground">
                        {{ description }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <DialogFooter class="mt-4 flex gap-2 sm:justify-end">
                <Button variant="outline" :disabled="loading" @click="handleClose">
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="variant === 'destructive' ? 'destructive' : 'default'"
                    :disabled="loading"
                    @click="emit('confirm')"
                >
                    <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
