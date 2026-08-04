<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links?: LinkItem[];
    from?: number | null;
    to?: number | null;
    total?: number;
    class?: string;
}

const props = defineProps<Props>();

// Filter links to remove previous and next from page numbers array
const pageLinks = computed(() => {
    if (!props.links) return [];
    return props.links.filter(
        (link) =>
            !link.label.includes('&laquo;') &&
            !link.label.includes('Previous') &&
            !link.label.includes('&raquo;') &&
            !link.label.includes('Next')
    );
});

const prevLink = computed(() => {
    if (!props.links) return null;
    return props.links.find(
        (link) =>
            link.label.includes('&laquo;') || link.label.includes('Previous')
    );
});

const nextLink = computed(() => {
    if (!props.links) return null;
    return props.links.find(
        (link) =>
            link.label.includes('&raquo;') || link.label.includes('Next')
    );
});

const cleanLabel = (label: string) => {
    return label.replace('&laquo;', '').replace('&raquo;', '').trim();
};
</script>

<template>
    <div
        v-if="links && links.length > 3"
        :class="[
            'flex flex-col sm:flex-row items-center justify-between gap-4 px-2 py-3 border-t border-border/60 dark:border-slate-800/80',
            props.class
        ]"
    >
        <!-- Info Text -->
        <div class="text-xs text-muted-foreground text-center sm:text-left">
            <template v-if="from && to && total">
                Showing <span class="font-semibold text-foreground">{{ from }}</span> to <span class="font-semibold text-foreground">{{ to }}</span> of <span class="font-semibold text-foreground">{{ total }}</span> entries
            </template>
            <template v-else-if="total">
                Total <span class="font-semibold text-foreground">{{ total }}</span> entries
            </template>
        </div>

        <!-- Navigation Links -->
        <div class="flex items-center gap-1">
            <!-- Previous Button -->
            <component
                :is="prevLink?.url ? Link : 'span'"
                :href="prevLink?.url || '#'"
                preserve-scroll
                preserve-state
                :class="[
                    'inline-flex h-8 items-center justify-center rounded-md px-2.5 text-xs font-medium transition-colors',
                    prevLink?.url
                        ? 'text-foreground hover:bg-accent hover:text-accent-foreground cursor-pointer border border-input dark:border-slate-800'
                        : 'text-muted-foreground/40 cursor-not-allowed border border-transparent'
                ]"
            >
                <ChevronLeft class="h-4 w-4 mr-0.5" />
                <span>Prev</span>
            </component>

            <!-- Page Number Buttons (Hidden on tiny screens if too many) -->
            <div class="hidden sm:flex items-center gap-1">
                <template v-for="(link, key) in pageLinks" :key="key">
                    <span
                        v-if="!link.url"
                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-md text-xs text-muted-foreground px-2"
                    >
                        {{ cleanLabel(link.label) }}
                    </span>
                    <Link
                        v-else
                        :href="link.url"
                        preserve-scroll
                        preserve-state
                        :class="[
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-md text-xs font-medium transition-colors px-2.5',
                            link.active
                                ? 'bg-cyan-600 text-white font-semibold shadow-sm shadow-cyan-600/20 dark:bg-cyan-500'
                                : 'text-foreground hover:bg-accent hover:text-accent-foreground border border-input dark:border-slate-800'
                        ]"
                    >
                        {{ cleanLabel(link.label) }}
                    </Link>
                </template>
            </div>

            <!-- Next Button -->
            <component
                :is="nextLink?.url ? Link : 'span'"
                :href="nextLink?.url || '#'"
                preserve-scroll
                preserve-state
                :class="[
                    'inline-flex h-8 items-center justify-center rounded-md px-2.5 text-xs font-medium transition-colors',
                    nextLink?.url
                        ? 'text-foreground hover:bg-accent hover:text-accent-foreground cursor-pointer border border-input dark:border-slate-800'
                        : 'text-muted-foreground/40 cursor-not-allowed border border-transparent'
                ]"
            >
                <span>Next</span>
                <ChevronRight class="h-4 w-4 ml-0.5" />
            </component>
        </div>
    </div>
</template>
